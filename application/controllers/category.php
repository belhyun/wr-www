<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class Category extends base_controller 
{
	function list_get()
	{
		try{
			$result = $this->wr_category->getList();
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
		$msgAry = wr_http_message::get200();
		$msgAry['categories'] = $result;
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}

	function room_post()
	{
		$cat_id = $this->input->post('cat_id');
		$page = $this->input->post('page');
		$orderId = $this->input->post('order');

		if($cat_id == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		if($page == null) $page = 1;
		if($orderId == null) $orderId = 1;

		try{
			$this->wr_category->setData(array('order_id' => $orderId, 'category_id'=>$cat_id,'page'=>$page));
			$result = $this->wr_category->getRoomList();
			$totalCnt = $this->wr_category->recordCount();
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
		$msgAry = wr_http_message::get200();
		$msgAry['totalRoomCount'] = $totalCnt;
		$msgAry['roomList'] = $result;
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}

	function room_cnt_get()
	{
		try{
			$result = $this->wr_category->getRoomCnt();
		}catch(Exception $ex){
			if($ex->getCode() == wr_http_message::ERROR_500)
			{
				$this->response(wr_http_message::get500($ex->getMessage()), wr_http_message::ERROR_500);
			}
		}
		$msgAry = wr_http_message::get200();
		$msgAry['allCategoriesRoomCount'] = $result;
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}
}

?>
