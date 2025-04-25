<?php

use yii\db\Migration;

/**
 * Class m240823_122824_produkti
 */
class m240823_122824_produkti extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%produkti}}', [
            'ProduktaNr' => $this->string(50)->notNull(),            
            'Apraksts' => $this->string(100),
            'Tilpums' => $this->float()->defaultValue(0),
            'NetoSvars' => $this->float()->defaultValue(0),
            'IepakojumaTips' => $this->string(50),
            'Izkartojums' => $this->integer()->notNull(),
            'PakasGarums' => $this->integer()->notNull(),
            'PakasPlatums' => $this->integer()->notNull(),
            'PakasAugstums' => $this->integer()->notNull(),
            'BruttoSvars' => $this->float()->defaultValue(0),
            'BazesMervieniba' => $this->string(50),
            'PrecuBrends' => $this->string(50),
            'ProduktiIepakojuma' => $this->integer()->notNull(),
            'ProduktiRinda' => $this->integer()->notNull(),
            'ProduktiPalete' => $this->integer()->notNull(),            
            'RealizacijasTermins' => $this->integer()->notNull(),
            'ProduktaVeids' => $this->string(100),
            'PudelesTips' => $this->integer(2),//Negāzēts->0, gāzēts->1, viegli gāzēts->2, stikls->3
            'barcode' => $this->bigInteger(20)->notNull(),
            'ProduktaNosaukums' => $this->string(300)->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci'),
            'LinijasAtrums' => $this->integer()->defaultValue(4700),
            'PRIMARY KEY(ProduktaNr)',
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%produkti}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240823_122824_produkti cannot be reverted.\n";

        return false;
    }
    */
}
