<?php

	if(intval($this->user['id_user']) == 0){
		header("Location: login");
		exit();
	}

	if($_POST['action'] && $_POST['couponCode'] != NULL){
		$coupon = $this->apiLoad('business')->businessCouponGet(array(
			'couponCode'	=> $_POST['couponCode'],
			'debug'			=> false
		));

		if($coupon['id_coupon'] == NULL){
			$COUPON_NOT_EXISTS = true;
		}else{

			$coupon_ = $this->apiLoad('business')->businessCouponGet(array(
				'couponCode'	=> $_POST['couponCode'],
				'id_user'		=> $this->user['id_user'],
				'is_used' 		=> '0',
				'debug'			=> false
			));

			if($coupon_['id_coupon'] == NULL){
				$this->apiLoad('business')->businessCouponSet(array(
					'id_user'	=> $this->user['id_user'],
					'id_coupon'	=> $coupon['id_coupon'],
					'debug'		=> false
				));
			}else{
				$ALREADY_INSERTED = true;
			}
			
		}

		
	}

	$myCoupon = $this->apiLoad('business')->businessCouponGet(array(
		'id_user'	=> $this->user['id_user'],
		'debug'		=> false
	));
?>