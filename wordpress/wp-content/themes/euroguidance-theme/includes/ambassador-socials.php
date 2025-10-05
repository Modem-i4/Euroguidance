<?php
add_action('init', function () {
	$pts = get_post_types(['show_in_rest'=>true],'names');
	$enum = ['website','linkedin','facebook','instagram'];
	foreach ($pts as $pt) {
		register_post_meta($pt,'ntd_social_links',[
			'single'=>true,
			'type'=>'array',
			'default'=>[],
			'show_in_rest'=>[
				'schema'=>[
					'type'=>'array',
					'items'=>[
						'type'=>'object',
						'properties'=>[
							'type'=>['type'=>'string','enum'=>$enum],
							'url'=>['type'=>'string','format'=>'uri'],
						],
						'required'=>['type','url'],
						'additionalProperties'=>false,
					],
				],
			],
			'auth_callback'=>fn($a,$k,$id)=>current_user_can('edit_post',$id),
			'sanitize_callback'=>'ntd_social_links_sanitize',
		]);
	}
});

function ntd_social_links_sanitize($v){
	$allowed=['website','linkedin','facebook','instagram'];
	if(!is_array($v)){
		$v=is_string($v)?json_decode($v,true):[];
		if(!is_array($v)) $v=[];
	}
	$out=[];
	foreach($v as $r){
		$t=isset($r['type'])?sanitize_key($r['type']):'';
		$u=isset($r['url'])?esc_url_raw($r['url']):'';
		if($t && in_array($t,$allowed,true)) $out[$t]=['type'=>$t,'url'=>$u];
	}
	return array_values($out);
}
