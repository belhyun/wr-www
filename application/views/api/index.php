<h1>Api page</h1>
버전체크
<?php
$attribute['method'] = 'get';
echo form_open('http://14.63.198.222/version',$attribute);
echo form_submit('submit','submit');
echo form_close();
?>
<hr/>
로그인
<?php
	echo form_open('http://14.63.198.222/login');
	$data = array(
			'name' => 'user_id',
			'id' => 'user_id',
			'value' => '이메일 주소'
			);
	echo form_input($data);
	$data = array(
			'name' => 'user_pw',
			'id' => 'user_pw',
			'value' => '비밀번호'
			);
	echo form_input($data);
	$data = array(
			'name' => 'gcm_id',
			'id' => 'gcm_id',
			'value' => 'gcm_id'
			);
	echo form_input($data);
	$data = array(
			'request_type' => 'request_type'
			);
	echo form_input($data);

	echo form_submit('submit','submit');
	echo form_close();
?>
<hr/>
회원가입
<?php
echo form_open('http://14.63.198.222/join');
$data = array(
		'name' => 'user_name',
		'id' => 'user_name',
		'value' => '이름'
		);
echo form_input($data);
$data = array(
		'name' => 'user_id',
		'id' => 'user_id',
		'value' => '이메일 주소'
		);
echo form_input($data);
$data = array(
		'name' => 'user_pw',
		'id' => 'uwer_pw',
		'value' => '비밀번호'
		);
$data = array(
		'name' => 'int_cat',
		'id' => 'int_cat',
		'value' => '관심 카테고리'
		);
echo form_input($data);
$data = array(
		'name' => 'int_cat',
		);
echo form_input($data);
echo form_submit('submit','submit');
echo form_close();
?>
