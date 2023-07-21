<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UserExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('translate_roles', [$this, 'translateRoles']),
        ];
    }

    public function translateRoles(array $roles): string
    {
        $translations = [
            'ROLE_ADMIN' => 'Administrateur',
            'ROLE_USER' => 'Utilisateur',
        ];

        $translated = [];
        foreach ($roles as $role) {
            $translated[] = $translations[$role];
        }

        return join(', ', $translated);
    }
}
