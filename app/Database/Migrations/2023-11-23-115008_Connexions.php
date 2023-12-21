<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class Connexions extends Migration
{
    /**
     * @var string[]
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var \Config\Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }
    public function up()
    {
        $fields = [
            'codeconnect'    => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
        ];

        $this->forge->addColumn($this->tables['identities'], $fields);
    }

    public function down()
    {
        # drop the fields
        $this->forge->dropColumn('table_name', ["codeconnect"]);
    }
}
