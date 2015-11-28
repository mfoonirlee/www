<?php if ($this->_var['pictures']): ?>
 <div class="picture" id="imglist">
		
             <?php $_from = $this->_var['pictures']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'picture');$this->_foreach['no'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['no']['total'] > 0):
    foreach ($_from AS $this->_var['picture']):
        $this->_foreach['no']['iteration']++;
?>
             
             <?php if ($this->_foreach['no']['iteration'] < 2): ?>
            
           <a  href="<?php echo $this->_var['picture']['img_url']; ?>"
    	rel="zoom1" 
        rev="<?php echo $this->_var['picture']['img_url']; ?>"
        title="<?php echo htmlspecialchars($this->_var['picture']['img_desc']); ?>">
        <img src="<?php if ($this->_var['picture']['thumb_url']): ?><?php echo $this->_var['picture']['thumb_url']; ?><?php else: ?><?php echo $this->_var['picture']['img_url']; ?><?php endif; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" class="onbg" /></a>
           <?php else: ?>
  <a  href="<?php echo $this->_var['picture']['img_url']; ?>"
    	rel="zoom1" 
        rev="<?php echo $this->_var['picture']['img_url']; ?>"
        title="<?php echo htmlspecialchars($this->_var['picture']['img_desc']); ?>">
        <img src="<?php if ($this->_var['picture']['thumb_url']): ?><?php echo $this->_var['picture']['thumb_url']; ?><?php else: ?><?php echo $this->_var['picture']['img_url']; ?><?php endif; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" class="autobg" /></a>
          <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
           
</div>
 
<?php endif; ?>
<script type="text/javascript">
	mypicBg();
</script>