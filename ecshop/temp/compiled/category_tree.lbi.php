
<div id="category_tree">
  <div class="tit">所有商品分类</div>
  <dl class="clearfix" >
    <div class="box1 cate" id="cate"> 
      <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');$this->_foreach['no'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['no']['total'] > 0):
    foreach ($_from AS $this->_var['cat']):
        $this->_foreach['no']['iteration']++;
?> 
      <h1 onclick="tab(<?php echo ($this->_foreach['no']['iteration'] - 1); ?>)"  
		
		
		<?php if (($this->_foreach['no']['iteration'] <= 1)): ?> 
      style="border-top:none" 
      <?php endif; ?> 
      
      > <span class="f_l"><img src="themes/ecmoban_shishangqiyi/images/btn_fold.gif" style="padding-top:10px;padding-right:6px;cursor:pointer;"></span> <a href="<?php echo $this->_var['cat']['url']; ?>" class="  f_l"><?php echo htmlspecialchars($this->_var['cat']['name']); ?></a>
      </h1>
      <ul style="display:none" >
        <?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?> 
        <a class="over_2" href="<?php echo $this->_var['child']['url']; ?>"><?php echo htmlspecialchars($this->_var['child']['name']); ?></a>
        <div class="clearfix"> 
          <?php $_from = $this->_var['child']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'childer');if (count($_from)):
    foreach ($_from AS $this->_var['childer']):
?> 
          <a class="over_3" href="<?php echo $this->_var['childer']['url']; ?>"><?php echo htmlspecialchars($this->_var['childer']['name']); ?></a> 
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
      <div style="clear:both"></div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
    </div>
  </dl>
</div>
<div class="blank"></div>
<script type="text/javascript">
function tab(id)
{ 	
	div_obj = document.getElementsByTagName("div");
	for(i=0; i<div_obj.length; i++)
	{
		if(div_obj[i].id == 'cate')
		{
			cate_div = div_obj[i];
		}
	}

	var obj_h4 = cate_div.getElementsByTagName("h4");
	var obj_ul = cate_div.getElementsByTagName("ul");
	var obj_img = cate_div.getElementsByTagName("img");

	if(obj_ul[id].style.display == "none")
	{
		obj_ul[id].style.display = "block";
		obj_img[id].src = "themes/ecmoban_shishangqiyi/images/btn_unfold.gif";
		return false;
	}
	else(obj_ul[id].style.display == "block")
	{
		obj_ul[id].style.display = "none";
		obj_img[id].src = "themes/ecmoban_shishangqiyi/images/btn_fold.gif";
	}
}
</script>