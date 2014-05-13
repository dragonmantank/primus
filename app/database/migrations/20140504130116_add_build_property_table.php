<?php

use Phinx\Migration\AbstractMigration;

class AddBuildPropertyTable extends AbstractMigration
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
        $table = $this->table('buildproperty');
        $table
            ->addColumn('project_id', 'integer')
            ->addColumn('property', 'string')
            ->addColumn('propertyValue', 'string')
            ->addForeignKey('project_id', 'project', 'id', array('delete' => 'CASCADE', 'update' => 'NO_ACTION'))
            ->save()
        ;
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('build_properties');
    }
}