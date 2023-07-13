<?php

namespace App\Security;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Repository\ResetPasswordRepository;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class ResetPasswordHelper
{
    /**
     * @param ResetPasswordRepository $resetPasswordRepository
     * @param MailerInterface         $mailer
     * @param string                  $contactEmail
     * @param string                  $contactName
     * @param string                  $secret
     */
    public function __construct(
        private ResetPasswordRepository $resetPasswordRepository,
        private MailerInterface $mailer,
        private string $contactEmail,
        private string $contactName,
        private string $secret
    ) {
    }

    /**
     * Sends the reset password email.
     */
    public function processSendingEmail(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $requestedAt = new DateTimeImmutable();
        $expiredAt = $requestedAt->modify('+1hours +0minutes +0seconds');
        $token = $this->generateToken($user);
        $resetToken = (new ResetPassword())
            ->setUser($user)
            ->setRequestedAt($requestedAt)
            ->setExpiredAt($expiredAt)
            ->setHashedToken($token)
        ;

        $this->resetPasswordRepository->save($resetToken, true);

        $email = (new TemplatedEmail())
            ->from(new Address($this->contactEmail, $this->contactName))
            ->to($user->getEmail())
            ->subject('Votre demande de réinitilisation de mot de passe')
            ->htmlTemplate('emails/reset.html.twig')
            ->context([
                'resetPassword' => $resetToken,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    /**
     * Returns the user.
     */
    public function getUserFromResetToken(Request $request): ?User
    {
        $resetPassword = $this->retrieveResetPassword($request);

        if (null === $resetPassword) {
            return null;
        }

        $current = new DateTimeImmutable();

        if ($current > $resetPassword->getExpiredAt()) {
            $this->resetPasswordRepository->remove($resetPassword);

            return null;
        }

        $token = $this->generateToken($resetPassword->getUser());

        if (!hash_equals($token, $resetPassword->getHashedToken())) {
            $this->resetPasswordRepository->remove($resetPassword);

            return null;
        }

        return $resetPassword->getUser();
    }

    /**
     * Removes a reset password request from the database.
     */
    public function removeResetPassword(Request $request): void
    {
        $resetPassword = $this->retrieveResetPassword($request);

        if ($resetPassword) {
            $this->resetPasswordRepository->remove($resetPassword);
        }
    }

    /**
     * Generates a hashed token.
     */
    private function generateToken(User $user): string
    {
        $encodedData = json_encode([$user->getId(), $user->getEmail()]);

        return base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
    }

    /**
     * Returns the reset password.
     */
    private function retrieveResetPassword(Request $request): ?ResetPassword
    {
        $token = $request->get('token');

        if (!$token) {
            return null;
        }

        $resetPassword = $this->resetPasswordRepository->findOneBy(['hashedToken' => $token]);

        if (!$resetPassword) {
            return null;
        }

        return $resetPassword;
    }
}
