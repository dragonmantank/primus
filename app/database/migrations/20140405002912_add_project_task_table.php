<?php

use Phinx\Migration\AbstractMigration;

class AddProjectTaskTable extends AbstractMigration
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
        $projectTasks = $this->table('projecttask');
        $projectTasks
            ->addColumn('project_id', 'integer')
            ->addColumn('task', 'string')
            ->addIndex(array('project_id', 'task'), array('unique' => true))
            ->addForeignKey('project_id', 'project', 'id', array('delete' => 'CASCADE'))
            ->save()
        ;
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('projecttask');
    }
}
