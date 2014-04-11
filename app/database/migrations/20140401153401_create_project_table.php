<?php

use Phinx\Migration\AbstractMigration;

class CreateProjectTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
     */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $projects = $this->table('project');
        $projects
            ->addColumn('name', 'string')
            ->addColumn('repo', 'string')
            ->addColumn('repoName', 'string')
            ->addColumn('branch', 'string')
            ->addColumn('deployPath', 'string')
            ->addColumn('active', 'boolean', array('default' => true))
            ->addIndex(array('name', 'branch'), array('unique' => true))
            ->save()
        ;
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('projects');
    }
}
