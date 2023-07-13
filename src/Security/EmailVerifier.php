<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailVerifier
{
    /**
     * @var integer the life time of a token
     */
    private int $lifetime = 3600;

    /**
     * @param MailerInterface $mailer
     * @param EntityManagerInterface $entityManager
     * @param string $contactEmail
     * @param string $contactName
     * @param string $secret
     */
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private string $contactEmail,
        private string $contactName,
        private string $secret
    ) {
    }

    /**
     * Sends the conformation email.
     *
     * @return bool the success status
     */
    public function sendConfirmationRequest(User $user, string $template, string $route): bool
    {
        $context = $this->generateToken($user);
        $context['route'] = $route;

        $email = (new TemplatedEmail())
                ->from(new Address($this->contactEmail, $this->contactName))
                ->to($user->getEmail())
                ->subject('Confirmez votre mot de passe')
                ->htmlTemplate("emails/$template.html.twig")
                ->context($context)
            ;

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $th) {
            return false;
        }
    }

    /**
     * Checks the token and update user status in case of success.
     *
     * @return bool the success status
     */
    public function handleEmailConfirmation(string $token, int $expiredAtTimestamp, User $user): bool
    {
        try {
            $this->checkToken($token, $expiredAtTimestamp, $user);
            $user->setIsActive(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $th) {
            return false;
        }
    }

    /**
     * Generates a token and returns an array of params for the email template.
     *
     * @return array the params for the view
     */
    private function generateToken(User $user): array
    {
        $generatedAt = time();
        $expiredAtTimestamp = $generatedAt + $this->lifetime;
        $encodedData = json_encode([$user->getId(), $user->getEmail()]);
        $token = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));

        return [
            'user' => $user,
            'token' => $token,
            'expiredAt' => \DateTimeImmutable::createFromFormat('U', (string) $expiredAtTimestamp),
            'expiredAtTimestamp' => $expiredAtTimestamp,
        ];
    }

    /**
     * Checks if the token is valid.
     */
    private function checkToken(string $token, int $expiredAtTimestamp, User $user): void
    {
        $current = \DateTimeImmutable::createFromFormat('U', (string) time());
        $limit = \DateTimeImmutable::createFromFormat('U', (string) $expiredAtTimestamp);

        if ($current > $limit) {
            throw new \Exception('Invalid Token');
        }

        $encodedData = json_encode([$user->getId(), $user->getEmail()]);
        $currentToken = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));

        if (!hash_equals($currentToken, $token)) {
            throw new \Exception('Invalid Token');
        }
    }

    /**
     * Get the value of lifetime.
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Set the value of lifetime.
     */
    public function setLifetime(int $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }
}
