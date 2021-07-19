<?php

/**
 * Card Entity
 */
class Card implements JsonSerializable {
    /** card id */
    private $id;

    /** card img path */
    private $cardImg;

    public function JsonSerialize()
    {
        return (object) get_object_vars($this);
    }


    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCardImg($cardImg)
    {
        $this->cardImg = $cardImg;
    }

    public function getCardImg()
    {
        return $this->cardImg;
    }
}
