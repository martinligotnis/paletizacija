<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_metrics}}`.
 */
class m250424_092308_create_product_metrics_table extends Migration
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

        // Create product_metrics table
        $this->createTable('{{%product_metrics}}', [
            'ProduktaNr' => $this->string(255)->notNull(),
            'avg_interval_seconds' => $this->float()->notNull(),
            'p25_interval_seconds' => $this->float()->notNull(),
            'p75_interval_seconds' => $this->float()->notNull(),
            'last_updated' => $this->dateTime()->notNull(),
            'PRIMARY KEY(ProduktaNr)',
        ], $tableOptions);

        // Add foreign key to paletes table
        $this->addForeignKey(
            'fk-product_metrics-ProduktaNr',
            '{{%product_metrics}}',
            'ProduktaNr',
            '{{%paletes}}',
            'ProduktaNr',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey(
            'fk-product_metrics-ProduktaNr',
            '{{%product_metrics}}'
        );

        // Drop table
        $this->dropTable('{{%product_metrics}}');
    }
}
