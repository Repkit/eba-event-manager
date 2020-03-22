<?php

namespace MicroIceEventManager\V1\Rest\Events;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventsEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'DestinationId' => null,
      'StartDate' => null,
      'EndDate' => null,
      'CreationDate' => null,
      'Status' => null,
    );

    public function validate()
    {
    	$start = $this->_data['StartDate'];
    	$end = $this->_data['EndDate'];

    	if(!empty($end)){
    		if(!empty($start)){
    			$s = new \DateTime($start);
				$e = new \DateTime($end);
				// http://php.net/manual/en/datetime.diff.php#example-2554
				if($s > $e){
					throw new \InvalidArgumentException("StartDate can not be greater than EndDate", 1);
				}
    		}else{
    			throw new \InvalidArgumentException("Invalid StartDate", 1);
    		}
    	}

    	return true;
    }
}