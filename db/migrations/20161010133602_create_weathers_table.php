<?php

use Phinx\Migration\AbstractMigration;

class CreateWeathersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $dataWeather = $this->table('weathers');
        
        $dataWeather
            ->addColumn('day_short', 'string')
            ->addColumn('day_name_short', 'string')
            ->addColumn('day_comment_short', 'string')
            ->addColumn('max_temp_day_short', 'string')
            ->addColumn('min_temp_day_short', 'string')
            ->addColumn('day_weekley', 'string')
            ->addColumn('day_month', 'string')
            ->addColumn('day_number', 'integer')
            ->addColumn('sunset', 'string', ['null' => true])
            ->addColumn('sunrise', 'string', ['null' => true])
            ->addColumn('change_id', 'integer')
            ->addIndex('change_id')
            ->addForeignKey('change_id', 'changes', 'id', [
                'delete'=> 'CASCADE',
                'update'=> 'CASCADE'
            ])
            ->save();

    }

    public function down()
    {
        $exist = $this->hasTable('weathers');
        if($exist){
            $weathers = $this->table('weathers');
            $weathers->dropForeignKey('change_id')->drop();
        }
    }
}
