<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class article_redis extends base_controller 
{
	function write_post()
	{
		$roomId = $this->input->post('room_id'); 		
		$userId = $this->input->post('user_id');
		$content = $this->input->post('content');
		
		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		
		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_article_redis->setData(array('room_id'=>$roomId,'user_id'=>$userId,
						'content' => $content, 'article_type'=>1));

			$msgId = $this->wr_article_redis->write();
			$msgAry = wr_http_message::get200();
			$msgAry['messageId'] = $msgId;
			$this->response($msgAry, wr_http_message::SUCCESS_200);
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function delete_post()
	{
		$userId = $this->input->post('user_id');
		$articleId = $this->input->post('article_id');

		if($userId == null || $articleId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_article->setData(array('id'=>$articleId,'user_id'=>$userId));
			if($this->wr_article->delete())
			{
				$msgAry = wr_http_message::get200();
				$msgAry['messageId'] = $articleId;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}

			//$this->response(wr_http_message:get404(),wr_http_message::NOT_FOUND_404);
			$this->response(wr_http_message::get404(), wr_http_message::NOT_FOUND_404);
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}

	}

	function write_rly_post()
	{
		$userId = $this->input->post('user_id');
		$articleId = $this->input->post('article_id');
		$content = $this->input->post('content');
		$roomId = $this->input->post('room_id');


		if($userId == null || $articleId == null || $content == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_article->setData(array('user_id'=>$userId,'parent_id'=>$articleId,
			'content'=>$content,'article_type'=>2,'room_id'=>$roomId));
			if($this->wr_article_redis->write())
			{
				$msgAry = wr_http_message::get200();
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}

	}

	function get_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');
		$page = $this->input->post('page');

		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		if($page == null)
		{
			$page = 0;
		}

		try{
			$this->wr_article->setData(array('user_id'=>$userId,'room_id'=>$roomId,'page'=>$page));
			$this->wr_notice->setData(array('room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId));
			$msgAry = wr_http_message::get200();
			$articleList = $this->wr_article->get();
			$notice = $this->wr_notice->select();
			$msgAry['messageList'] = $articleList;
			if(!$notice)
				$msgAry['roomManagerNotice'] = '';
			else 
				$msgAry['roomManagerNotice'] = $notice->content;
			$msgAry['totalBoardCount'] = count($articleList);
			$roomInfo = $this->wr_room->getRoomManagerInfo();
			if($roomInfo)
			{
				$msgAry['roomManagerPurpose'] = $roomInfo->roomManager;
				$msgAry['roomManagerName'] = $roomInfo->roomManagerName;
				$msgAry['roomManagerId'] = $roomInfo->roomManagerId;
				$msgAry['roomManageImagePath'] = $roomInfo->roomManageImagePath;
			}
			$this->response($msgAry, wr_http_message::SUCCESS_200);
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}
	}

	function get_rly_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');
		$parentId = $this->input->post('parent_id');
		$page = $this->input->post('page');

		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		if($page == null)
		{
			$page = 0;
		}

		try{
			$this->wr_article->setData(array('user_id'=>$userId,'room_id'=>$roomId,'page'=>$page,'parent_id'=>$parentId));
			$this->wr_notice->setData(array('room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId));
			$msgAry = wr_http_message::get200();
			$articleList = $this->wr_article->getRly();
			$msgAry['messageList'] = $articleList;
			$msgAry['totalBoardCount'] = count($articleList);
			$this->response($msgAry, wr_http_message::SUCCESS_200);
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}

	}
}
?>
