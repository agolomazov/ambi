<?php

use Phinx\Migration\AbstractMigration;

class CreateWeatherDetailsTable extends AbstractMigration
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

        $weatherDetails = $this->table('weather_details');

        $weatherDetails->addColumn('day_part', 'string')
            ->addColumn('temp', 'string')
            ->addColumn('condition', 'string')
            ->addColumn('air_presure', 'string')
            ->addColumn('type_wind', 'string')
            ->addColumn('weather_id', 'integer')
            ->addIndex('weather_id')
            ->addForeignKey('weather_id', 'weathers', 'id', [
                'delete'=> 'CASCADE',
                'update'=> 'CASCADE'
            ])
            ->save();
    }

    public function down(){
        $exist = $this->hasTable('weather_details');
        if($exist){
            $weather_details = $this->table('weather_details');
            $weather_details->dropForeignKey('weather_id')->drop();
        }
    }
}
