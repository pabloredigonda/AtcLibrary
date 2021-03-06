<?php
namespace Core\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class DataTableDTO
 *
 * @category General
 * @package  Core\Dto
 * @author   Pablo Redigonda <pablo.redigonda@globant.com>
 */
class DataTableDTO extends ResponseDTO
{
    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
    public $sEcho = 1;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
    public $aaData = array();

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
    public $iTotalDisplayRecords;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
    public $iTotalRecords;

    /**
     * setEcho
     *
     * @param Bool $echo
     *
     * @return mixed
     */
    public function setEcho($echo)
    {
		$this->sEcho = $echo;
	}

    /**
     * getEcho
     *
     * @return mixed
     */
    public function getEcho()
    {
		return $this->sEcho;
	}

    /**
     * setTotalDisplayRecords
     *
     * @param $totalDisplayRecords
     *
     * @return mixed
     */
    public function setTotalDisplayRecords($totalDisplayRecords)
    {
		$this->iTotalDisplayRecords = $totalDisplayRecords;
	}

    /**
     * getTotalDisplayRecords
     *
     * @return mixed
     */
    public function getTotalDisplayRecords()
    {
		return $this->iTotalDisplayRecords;
	}

    /**
     * @param mixed $iTotalRecords Variable
     */
    public function setTotalRecords($iTotalRecords)
    {
        $this->iTotalRecords = $iTotalRecords;
    }

    /**
     * @return mixed
     */
    public function getTotalRecords()
    {
        return $this->iTotalRecords;
    }

    /**
     * addItem
     *
     * @param $item
     *
     * @return mixed
     */
    public function addItem($item)
    {
		array_push($this->aaData, $item);
	}

    /**
     * addItems
     *
     * @param $arrItems
     *
     * @return mixed
     */
    public function addItems($arrItems)
    {
		foreach($arrItems as $item) {
			$this->addItem($item);
		}
        $this->iTotalRecords = $this->iTotalDisplayRecords = count($this->aaData);
	}
}