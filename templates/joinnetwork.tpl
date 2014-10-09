{* template block that contains the Join Network field *}
<div id="join_our_network-block">
<div id="helprow-join_our_network" class="crm-section">
    <div class="content description">
        Please check the box below to Join CPEHN's Network
        <span class="bt-help-tooltip about-cpehns-network" bt-xtitle="" title="">
            :
        </span>
    </div>
</div>
<div id="join_our_network" class="crm-section form-item">
    <div class="label"></div>
    <div class="label"></div>
    <div class="content">
      {$form.join_our_network.html} {$form.join_our_network.label} 
    </div>
    <div class="clear"></div>

</div>
</div>
{* reposition the above block at the bottom of the profile fieldset *}
<script type="text/javascript">
  cj('#join_our_network-block').insertAfter('.crm-profile div:last');
</script>
