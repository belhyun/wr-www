<?php

class wr_article_redis extends CI_Model {
	var $id = '';
	var $user_id = '';
	var $room_id = '';
	var $content = '';
	var $reg_date = '';
	var $parent_id = '';
	var $article_type = '';
	var $page = '';

	function write()
	{
		$data = array('user_id' => $this->user_id,
				'content' => $this->content,
				'reg_date' => date('YmdHis'),
				'article_type' => $this->article_type,
				'room_id' => $this->room_id
				);

		if($this->article_type == 2 && $this->parent_id != null)
		{
			$data['parent_id'] = $this->parent_id;
			$key = "ROOM_{$this->room_id}_ARTICLE";
		}
		else
		{
			$key = "ROOM_{$this->room_id}_ARTICLE_RLY";
		}
		
		$redisData = serialize($data);
		$this->redis->command("SADD {$key} {$redisData}");
	}

	function get()
	{
		$this->db->select('parent_id AS id,COUNT(*) AS replyCount');
		$this->db->from('wr_article');
		$this->db->where('article_type',2);
		$this->db->group_by('parent_id');
		$ado = $this->db->get();
		$replyCntAry = $ado->result();

		$this->db->select('article.id AS messageId,user.id as writeId,user.name AS writerNickname,
				user.image AS writerImagePath ,article.reg_date AS writeTime,
				article.content AS message,article.article_type,article.parent_id AS
				parentId');
		$this->db->from('wr_article AS article LEFT JOIN wr_user AS user ON
				article.user_id = user.id');
		$this->db->where('article.room_id',$this->room_id);
		$this->db->where('article.article_type',1);
		$this->db->limit(10,$this->page);
		$this->db->order_by('article.parent_id asc');
		$this->db->order_by('article.id asc');
		$this->db->order_by('article.reg_date desc');
		$ado = $this->db->get();
		if($ado->num_rows() == 0)
		{
			return array();
		}
		else
		{
			$result = array();
			foreach($ado->result() as $article)
			{
				$writeTime = strtotime($article->writeTime);
				$diffTime = time()-$writeTime;
				if($diffTime <= 60)
				{
					$article->writeTime = $diffTime.'초전';
				}
				else if($diffTime > 60 && $diffTime <= 3600)
				{
					$article->writeTime = (int)($diffTime / 60).'분전';
				}
				else if($diffTime > 3600 && $diffTime <= 86400)
				{
					$article->writeTime = (int)($diffTime / 3600).'시간전';
				}
				else if($diffTime > 86400 && $diffTime <= 604800)
				{
					$article->writeTime = (int)($diffTime / 86400).'일전';
				}
				else
				{
					$article->writeTime = '몇달전';
				}
				if($article->article_type == '1')
				{
					$article->isReply = false;
				}
				else
				{
					$article->isReply = true;
				}
				$article->replyCount = '0';
				foreach($replyCntAry as $reply)
				{
					if($reply->id == $article->messageId)
					{
						$article->replyCount = $reply->replyCount;
					}
				}
				unset($article->article_type);
				$result[] = $article;
			}
			return $result;
		}
	}


	function getRly()
	{
		$this->db->select('article.id AS messageId,user.id as writeId,user.name AS writerNickname,
				user.image AS writerImagePath ,article.reg_date AS writeTime,
				article.content AS message,article.article_type,article.parent_id AS
				parentId');
		$this->db->from('wr_article AS article LEFT JOIN wr_user AS user ON
				article.user_id = user.id');
		$this->db->where('article.parent_id',$this->parent_id);
		$this->db->where('article.room_id',$this->room_id);
		$this->db->where('article.article_type',2);
		$this->db->limit(10,$this->page);
		$this->db->order_by('article.parent_id asc');
		$this->db->order_by('article.id asc');
		$this->db->order_by('article.reg_date desc');
		$ado = $this->db->get();
		if($ado->num_rows() == 0)
		{
			return array();
		}
		else
		{
			$result = array();
			foreach($ado->result() as $article)
			{
				$writeTime = strtotime($article->writeTime);
				$diffTime = time()-$writeTime;
				if($diffTime <= 60)
				{
					$article->writeTime = $diffTime.'초전';
				}
				else if($diffTime > 60 && $diffTime <= 3600)
				{
					$article->writeTime = (int)($diffTime / 60).'분전';
				}
				else if($diffTime > 3600 && $diffTime <= 86400)
				{
					$article->writeTime = (int)($diffTime / 3600).'시간전';
				}
				else if($diffTime > 86400 && $diffTime <= 604800)
				{
					$article->writeTime = (int)($diffTime / 86400).'일전';
				}
				else
				{
					$article->writeTime = '몇달전';
				}
				$article->isReply = true;
				unset($article->article_type);
				$result[] = $article;
			}
			return $result;
		}

	}

	function delete()
	{
		$this->db->where('id',$this->id);
		$this->db->where('user_id',$this->user_id);
		$this->db->from('wr_article');
		$ado = $this->db->get();
		if($ado->num_rows() == 0) return false;
		$this->db->from('wr_article');
		$this->db->where('id',$this->id);
		if($this->db->delete())
		{
			return true;
		}
		return false;
	}
}
