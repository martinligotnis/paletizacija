<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Produkti]].
 *
 * @see Produkti
 */
class PaletesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Produkti[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Produkti|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
