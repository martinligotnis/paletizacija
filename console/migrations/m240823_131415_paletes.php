<?php

use yii\db\Migration;

/**
 * Class m240823_131415_paletes
 */
class m240823_131415_paletes extends Migration
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

        $this->createTable('{{%paletes}}', [
            'ProduktaNr' => $this->string(50)->notNull(),
            'Apraksts' => $this->string(100)->notNull(),
            'ProduktiPalete' => $this->integer()->notNull(),   
            'DatumsLaiks' => $this->datetime(),
            'PaletesID' => $this->integer()->notNull(),         
            'RealizacijasTermins' => $this->date()->notNull(),
            'IsPrinted' => $this->tinyInteger()->defaultValue(0),
            'PRIMARY KEY(DatumsLaiks)',
        ], $tableOptions);

         // Create an index on ProduktaNr for better performance
         $this->createIndex(
            '{{%idx-paletes-ProduktaNr}}',
            '{{%paletes}}',
            'ProduktaNr'
        );

        // Add a foreign key constraint to reference the 'produkti' table
        $this->addForeignKey(
            '{{%fk-paletes-ProduktaNr}}',
            '{{%paletes}}',      // The table that contains the foreign key
            'ProduktaNr',        // The column in the paletes table that will hold the foreign key
            '{{%produkti}}',     // The table being referenced
            'ProduktaNr',        // The column in the referenced table
            'CASCADE',           // ON DELETE
            'CASCADE'            // ON UPDATE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the foreign key constraint
        $this->dropForeignKey(
            '{{%fk-paletes-ProduktaNr}}',
            '{{%paletes}}'
        );

        // Drop the index
        $this->dropIndex(
            '{{%idx-paletes-ProduktaNr}}',
            '{{%paletes}}'
        );

        // Drop the 'paletes' table
        $this->dropTable('{{%paletes}}');
    }
    
}
