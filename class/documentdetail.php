<?
class documentDetail{
	//public $arr;
	private $_table;
	private $_prefix;
	
	public function newDetial(){
		$array=get_object_vars($this);
		unset($array['_table']);
		unset($array['_prefix']);
		$array['prefix']=$this->_prefix;
			if (isset($array['num']))
					if (inseretSql($array,$this->_table))
						return true;
					else
						return false;
	}
	public function getDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		$arr;
		$list=selectSql($cond,$this->_table);
		if ($list){
			foreach ($list as $row){
				$bla=new documentDetail;
				foreach($row as $key=>$value)
					$bla->{$key}= $value;
				$arr[]=$bla;
			}
			return $arr;
		}
		return false;
	}
	public function updateDetials($array){
		//rellay ugly need some work in th nir fuetre
		//if (!is_null($this->getItem($array['num'])))
		//	return updateSql($cond,$array,$this->_table);
		$a=new documentDetail();
		$a->num=$this->num;
		if ($a->deleteDetials()){
			foreach ($array as $detial){
				//$a=new documentDetail;
				if (!$detial->newDetial()) return false;
			}
			return true;
		}
		return false;
	}
	public function deleteDetials(){
		$cond['prefix']=$this->_prefix;
		$cond['num']=$this->num;
		return deleteSql($cond,$this->_table);
	}
	public function __construct(){
		global $docdetailstbl;
		global $prefix;
		$this->_table = $docdetailstbl;
		$this->_prefix = $prefix;
		$values=listCol($this->_table);
		//foreach ($values as $value) $this->arr[$value['Field']]='';
		foreach($values as $value)
			$this->{$value['Field']}= '';
		return $this;
	}
}
?>