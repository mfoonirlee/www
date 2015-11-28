// JavaScript Document
$(document).ready(function(){
	$('.pic_next').click(function(){
		
		if($('.piclist').is(':animated')){
			$('.piclist').stop(true,true);
		}/* 避免点击事件重复 */
		
		ml = parseInt($('.piclist').css('left'));
		r = liw - (900 - ml);  /* 900为外部区块.infopic的宽度，15为li之间的距离，即.piclist li的margin-right的值 */
		if(r<920){
			s = r - 15;
		}else{
			s = 920;
		}
		$('.piclist').animate({left: ml - s + 'px'},'slow');			
	})
	
	$('.pic_prev').click(function(){
		
		if($('.piclist').is(':animated')){
			$('.piclist').stop(true,true);
		}/* 避免点击事件重复 */
		
		ml = parseInt($('.piclist').css('left'));
		if(ml>-920){
			s = ml;
		}else{
			s = -920;
		}
		$('.piclist').animate({left: ml - s + 'px'},'slow');			
	})
	
})
//info pic
$(window).load(function(){
	liw = 0;
	$('.piclist li').each(function(){
		liw += $(this).width()+15;
		$(this).css('width',$(this).width()+'px');
	})
	$('.piclist').width( liw + 'px');
})
