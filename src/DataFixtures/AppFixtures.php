<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Service\ImageUploader;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class AppFixtures.
 *
 * This class loads the starting data.
 */
class AppFixtures extends Fixture
{
    private const BANNER_IMAGE = '/public/assets/2e16d0bac75.jpg';

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader,
        private Filesystem $filesystem,
        private string $projectDir
    ) {
    }

    /**
     * Loads data fixtures: 10 starting tricks with a featured image, 3 categories and an admin user.
     */
    public function load(ObjectManager $manager): void
    {
        $user = $this->getUser($manager);
        $groups = $this->getGroups($manager);
        $tricks = $this->getTricksData();

        foreach ($tricks as $group => $elements) {
            /** 
             * @var Category 
             */
            $category = $groups[$group];

            foreach ($elements as $name => $description) {
                $trick = (new Trick())
                    ->setName($name)
                    ->setSlug(strtolower($this->slugger->slug($name)))
                    ->setDescription($description)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setUpdatedAt(new DateTime())
                    ->setAuthor($user)
                    ->addCategory($category)
                    ->setFeaturedImage($this->createImage($name))
                ;

                $manager->persist($trick);
            }
        }

        $manager->flush();
    }

    /**
     * Creates a new user.
     *
     * @return User an admin user
     */
    protected function getUser(ObjectManager $manager): User
    {
        $user = (new User())
            ->setUsername('Admin')
            ->setEmail('celinefoucart@yahoo.fr')
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsActive(true)
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $password = $this->hasher->hashPassword($user, 'XqTw78!FR45');
        $user->setPassword($password);
        $manager->persist($user);

        return $user;
    }

    /**
     * Creates 4 categories.
     *
     * @return Category[]
     */
    protected function getGroups(ObjectManager $manager): array
    {
        $groupNames = ['grabs', 'rotations', 'flips'];
        $groups = [];

        foreach ($groupNames as $name) {
            $group = (new Category())->setName($name);
            $manager->persist($group);
            $groups[$name] = $group;
        }

        return $groups;
    }

    /**
     * Gets an array of trick, grouped by type: grabs, flips and rotations.
     * The trick name is the key and the description is the value.
     *
     * @return array an array of tricks
     */
    protected function getTricksData(): array
    {
        return [
            'grabs' => [
                'Indy' => 'Attrape le carre des orteils de ta planche, entre les fixations, avec ta main arrière.',
                'Stalefish' => 'Passe la main derrière ton genou arrière et attrape le carre de ta planche entre les fixations, côté talon, avec ta main arrière.',
                'Weddle' => "(anciennement appelé Mute Grab) - Du nom de Chris Weddle, l'inventeur, attrape le carre des orteils entre les fixations avec ta main avant.",
                'Melon' => 'Passe la main avant derrière ton genou et attrape le bord des talons entre les fixations.',
            ],
            'flips' => [
                'Wildcat' => 'Un Wildcat est un Backflip qui garde la planche parallèle à la trajectoire, tu fais donc une sorte de Flip latéral sans perte de vitesse.',
                'Backflip' => "Un Backflip fait tourner la planche perpendiculairement à la neige, tu fais donc un Flip directement en arrière, en stabilisant la planche lors de l'atterrissage.",
                'Tamedog ' => "L'exact opposé d'un Wildcat est un Tamedog. C'est un Frontflip qui garde la planche parallèle à la trajectoire. Un hard Nollie utilise le nez comme tremplin pour amorcer la rotation.",
            ],
            'rotations' => [
                'Nose-Roll 180' => 'Amorce un virage sur les orteils ou les talons, et une fois que tu es sur la carre, soulève le talon de ta planche, en gardant la spatule au sol. Ensuite, fais pivoter la planche pour atterrir en Switch.',
                'Tail-Drag 180' => 'Le Tail drag consiste à amorcer un virage appuyé sur tes orteils ou tes talons, puis à faire un Ollie mais en gardant le talon de la planche au sol. Ensuite, fais-le glisser et atterris en Switch.',
                'Nose-Roll 360' => "Commence de la même manière que le Nose-Roll 180, mais saute plus haut et avec plus de force au niveau de la rotation. Lorsque ta planche est perpendiculaire à la trajectoire, soulève la spatule avant de la neige et fais un tour complet sur toi-même en l'air, c'est-à-dire un 360.",
            ],
        ];
    }

    /**
     * Creates an image object that represents a file uploaded on the server.
     *
     * @return string|null returns a file name in case of success or null
     */
    protected function createImage(): ?string
    {
        $imagePath = $this->projectDir.self::BANNER_IMAGE;

        if ($this->filesystem->exists($imagePath)) {
            $filename = uniqid().random_int(0, 1000).'.jpg';
            $this->filesystem->copy($imagePath, $this->projectDir.'/public/images/'.$filename);

            return $filename;
        }

        return null;
    }
}
