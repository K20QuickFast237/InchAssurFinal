<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'Particulier';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        // 'superadmin' => [
        //     'title'       => 'Super Admin',
        //     'description' => 'Complete control of the site.',
        // ],
        'administrateur' => [
            'title'       => 'Administrateur',
            'description' => 'Administre la plateforme en effectuant des configurations et autres opérations.',
        ],
        'assureur' => [
            'title'       => 'Assureur',
            'description' => 'Ajoute des Assurances.',
        ],
        'medecin' => [
            'title'       => 'Medecin',
            'description' => 'Effectue des consultations et propose ses services de consultation.',
        ],
        'entreprise' => [
            'title'       => 'Entreprise',
            'description' => 'Enregistre des utilisateurs (ses employés) et achete des produits en groupe (pour ses employés).',
        ],
        'famille'         => [
            'title'       => 'Famille',
            'description' => 'Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.',
        ],
        'particulier' => [
            'title'       => 'Particulier',
            'description' => 'Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.',
        ]
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [];/*
        'admin.access'        => 'Can access the sites admin area',
        'admin.settings'      => 'Can access the main site settings',
        'users.manage-admins' => 'Can manage other admins',
        'users.create'        => 'Can create new non-admin users',
        'users.edit'          => 'Can edit existing non-admin users',
        'users.delete'        => 'Can delete existing non-admin users',
        'beta.access'         => 'Can access beta-level features',
    ];*/

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [

        'administrateur' => ["pockets.*", "users.*", "assurances.*", "medecins.*", "sinistres.*", "incidents.*", "conversations.*", "consultations.*", "agendas.*"],
        'entreprise'     => ["*"],
        'assureur'       => ["*"],
        'medecin'        => ["*"],
        'famille'        => ["*"],
        'particulier'    => ["pockets.*", "users.*", "assurances.*", "sinistres.Create", "sinistres.view", "incidents.Create", "incidents.view"],
    ]; /* medecins.addAssur, assurances.addMed  /*
        'superadmin' => [
            'admin.*',
            'users.*',
            'beta.*',
        ],
        'admin' => [
            'admin.access',
            'users.create',
            'users.edit',
            'users.delete',
            'beta.access',
        ],
        'developer' => [
            'admin.access',
            'admin.settings',
            'users.create',
            'users.edit',
            'beta.access',
        ],
        'user' => [],
        'beta' => [
            'beta.access',
        ],
    ];*/
}
