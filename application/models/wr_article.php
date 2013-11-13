<?php

class wr_article extends CI_Model {
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
		$query = $this->db->query("SELECT MAX(id) AS id FROM wr_article");
		$row = $query->row();
		if(empty($this->parent_id))
		{
			if($row->id != null)
			{
				$this->parent_id = $row->id+1;
			}
		}

		$data = array('user_id' => $this->user_id,
				'content' => $this->content,
				'reg_date' => date('YmdHis'),
				'article_type' => $this->article_type,
				'parent_id' => $this->parent_id,
				'room_id' => $this->room_id
				);
		$this->db->insert('wr_article',$data);

		if($row->id == null)
		{
			$query = $this->db->query("SELECT MAX(id) AS id FROM wr_article");
			$row = $query->row();
			if($row->id != null)
			{
				$this->parent_id = $row->id;
			}
			$this->db->set('parent_id',$this->parent_id);
			$this->db->where('id',$row->id);
			$this->db->update('wr_article');
			return (string)$row->id;
		}
		return (string)(($row->id)+1);
	}

	function get()
	{
		$this->page = $this->page*10-10;
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
		//$this->db->order_by('article.parent_id asc');
		//$this->db->order_by('article.id asc');
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
					$article->writeTime = $diffTime.' seconds ago';
				}
				else if($diffTime > 60 && $diffTime <= 3600)
				{
					$article->writeTime = (int)($diffTime / 60).' minutes ago';
				}
				else if($diffTime > 3600 && $diffTime <= 86400)
				{
					$article->writeTime = (int)($diffTime / 3600).' hours ago';
				}
				else if($diffTime > 86400 && $diffTime <= 604800)
				{
					$article->writeTime = (int)($diffTime / 86400).' days ago';
				}
				else
				{
					$article->writeTime = ' months ago';
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
		//$this->db->order_by('article.parent_id asc');
		//$this->db->order_by('article.id asc');
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
					$article->writeTime = $diffTime.' seconds ago';
				}
				else if($diffTime > 60 && $diffTime <= 3600)
				{
					$article->writeTime = (int)($diffTime / 60).' minutes ago';
				}
				else if($diffTime > 3600 && $diffTime <= 86400)
				{
					$article->writeTime = (int)($diffTime / 3600).' hours ago';
				}
				else if($diffTime > 86400 && $diffTime <= 604800)
				{
					$article->writeTime = (int)($diffTime / 86400).' days ago';
				}
				else
				{
					$article->writeTime = ' months ago';
				}
				$article->isReply = true;
				unset($article->article_type);
				$result[] = $article;
			}
			return $result;
		}

	}

	function getArticleCnt()
	{
		$this->db->from('wr_article');
		$this->db->where('room_id',$this->room_id);
		$this->db->where('article_type',1);
		$ado = $this->db->get();
		return $ado->num_rows();
	}

	function delete()
	{
		$this->db->where('id',$this->id);
		$this->db->where('user_id',$this->user_id);
		$this->db->from('wr_article');
		$ado = $this->db->get();
		if($ado->num_rows() == 0)
		{
			throw new Exception('article_not_exists');
		}
		$this->db->from('wr_article');
		$this->db->where('id',$this->id);
		if($this->db->delete())
		{
			return true;
		}
		return false;
	}
}
