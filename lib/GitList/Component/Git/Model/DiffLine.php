<?php

namespace GitList\Component\Git\Model;

use GitList\Component\Git\Model\Line;

class DiffLine extends Line
{
    protected $numNew;
    protected $numOld;

    public function __construct($data, $numOld, $numNew)
    {
        parent::__construct($data);

        if (!empty($data)) {
            switch ($data[0]) {
                case '@':
				    $this->setNumNew('...');
				    $this->setNumOld('...');
				    break;
				case '-':
				    $this->setNumOld($numOld);
				    $this->setNumNew('');
				    break;
				case '+':
				    $this->setNumNew($numNew);
				    $this->setNumOld('');
				    break;
				default:
				    $this->setNumOld($numOld);
				    $this->setNumNew($numNew);
            }
        } else {
            $this->numOld = $numOld;
            $this->numNew = $numNew;
        }
    }

    public function getNumOld()
    {
        return $this->numOld;
    }

    public function setNumOld($num)
    {
        $this->numOld = $num;
    }

    public function getNumNew()
    {
        return $this->numNew;
    }

    public function setNumNew($num)
    {
        $this->numNew = $num;
    }
}
