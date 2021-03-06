<?php
class getregionmodel extends CI_Model
{
	function __construct()
	{
		parent::__construct ();
		$this->load->model ( 'api/common', 'common' );
		$this->load->database ();
	}
	function getdataofPro($sessionkey,$productid,$fromtime,$totime,$country,$limit){

		try {
			$userid = $this->common->getuseridbysessionkey ( $sessionkey );
			if ($userid)
			{
				$verify = $this->common->verifyproductbyproductid ( $userid, $productid );
				if ($verify)
				{
					$basicAct = $this->getProActivedata($productid, $fromtime, $totime,$country,$limit);
					$basicNew = $this->getProNewData($productid,$fromtime,$totime,$country,$limit);
					
					if ($basicAct)
					{
						$productinfo = array (
								'flag' => 2,
								'queryResult' =>array(
										'newusers'=>$basicNew,
										'activeusers'=>$basicAct
										)
						);
					}
					else
					{
						$productinfo = array (
								'flag' => - 4,
								'msg' => 'No data information'
						);
					}
				}
				else
				{
					$productinfo = array (
							'flag' => - 6,
							'msg' => 'Do not have permission'
					);
				}
				return $productinfo;
			}
			else
			{
				$productinfo = array (
						'flag' => - 2,
						'msg' => 'Sessionkey is invalide '
				);
				return $productinfo;
			}
		}
		catch ( Exception $ex )
		{
			$productinfo = array (
					'flag' => - 3,
					'msg' => 'DB Error'
			);
			return $productinfo;
		}
		
	}
	
	function getProNewData($productid,$fromtime,$totime,$country,$limit)
	{
		$dwdb = $this->load->database('dw',TRUE);
		if($limit==null){
			$sql="
				select   l.region,count(distinct f.deviceidentifier) as access
				from   ".$dwdb->dbprefix('fact_clientdata')."      f,
				         ".$dwdb->dbprefix('dim_date')."   d,
				         ".$dwdb->dbprefix('dim_product')."   p,
				         ".$dwdb->dbprefix('dim_location')."   l
				where    l.country = '$country'
				         and f.date_sk = d.date_sk
				         and f.product_sk = p.product_sk
				         and f.location_sk = l.location_sk
				         and d.datevalue between '$fromtime' and '$totime'
				         and p.product_id = '$productid' and f.isnew=1
				group by l.region
				order by access desc  ;
				
						";
				}
				else
				{
							$sql="
				select   l.region,count(distinct f.deviceidentifier) as access
				from   ".$dwdb->dbprefix('fact_clientdata')."      f,
				         ".$dwdb->dbprefix('dim_date')."   d,
				         ".$dwdb->dbprefix('dim_product')."   p,
				         ".$dwdb->dbprefix('dim_location')."   l
				where    l.country = '$country'
				         and f.date_sk = d.date_sk
				         and f.product_sk = p.product_sk
				         and f.location_sk = l.location_sk
				         and d.datevalue between '$fromtime' and '$totime'
				         and p.product_id = '$productid' and f.isnew=1
				group by l.region
				order by access desc  limit 0,$limit;	";
		}
		
		$query = $dwdb->query ( $sql );
		if ($query != null && $query->num_rows () > 0)
		{
			$ret=array();
			$queryarr  = $query->result_array();
			for($i=0;$i<count($queryarr);$i++)
			{
			$obj=array(
					"id"=>$i+1,
					"province"=> $queryarr[$i]['region'],
					"num"=> $queryarr[$i]['access']
			);
					array_push($ret, $obj);
			}
			return $ret;
		}
	}
	
	
	function getProActivedata( $productid, $fromtime, $totime,$country,$limit){
		$dwdb = $this->load->database ( 'dw', TRUE );
		if($limit==null){
			$sql="
			select   l.region,count(distinct f.deviceidentifier) as access
			from    ".$dwdb->dbprefix('fact_clientdata')."     f,
			      ".$dwdb->dbprefix('dim_date')."      d,
			      ".$dwdb->dbprefix('dim_product')."      p,
			       ".$dwdb->dbprefix('dim_location')."     l
			where    l.country = '$country'
			         and f.date_sk = d.date_sk
			         and f.product_sk = p.product_sk
			         and f.location_sk = l.location_sk
			         and d.datevalue between '$fromtime' and '$totime'
			         and p.product_id = '$productid'
			group by l.region
			order by access desc;
			
					";
		}
		else
		{
						$sql="
			select   l.region,count(distinct f.deviceidentifier) as access
			from    ".$dwdb->dbprefix('fact_clientdata')."     f,
			      ".$dwdb->dbprefix('dim_date')."      d,
			      ".$dwdb->dbprefix('dim_product')."      p,
			       ".$dwdb->dbprefix('dim_location')."     l
			where    l.country = '$country'
			         and f.date_sk = d.date_sk
			         and f.product_sk = p.product_sk
			         and f.location_sk = l.location_sk
			         and d.datevalue between '$fromtime' and '$totime'
			         and p.product_id = '$productid'
			group by l.region
			order by access desc limit 0,$limit;";
		}
		$query = $dwdb->query ( $sql );
		if ($query != null && $query->num_rows () > 0){
			$ret=array();
			$queryarr  = $query->result_array();
			for($i=0;$i<count($queryarr);$i++)
			{
				$obj=array(
						"id"=>$i+1,
						"province"=> $queryarr[$i]['region'],
						"num"=> $queryarr[$i]['access']
				);
			array_push($ret, $obj);
			}
			return $ret;
		}else{
			return false;
		}
	}

}