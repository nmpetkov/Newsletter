{assign var="pagesetterPubTypes" value=$plugin_parameters.Pagesetter.param.pagesetterPubTypes}
{modgetvar assign="pagesetter_useAPI" module="Newsletter" name="pagesetter_useAPI"}

<div class="z-formrow">
    <label for="nw-ps-useapi">{gt text="Use Pagesetter API"}</label>
    <input id="nw-ps-useapi" name="pagesetter_useAPI" type="checkbox" value="1" {if $pagesetter_useAPI}checked="checked"{/if} />
    <em class="z-formnote">{gt text='Slower but fetches entire publication.'}</em>
</div>

{assign var='j' value=1}
{foreach from=$pagesetterPubTypes item=pgPubType}
<div class="z-formrow">
    <label for="nw-ps-pubtype{$pgPubType.id}">{$pgPubType.title|safehtml}</label>
    <input type="checkbox" name="pagesetterTIDs[{$pgPubType}]" value="1" {if $pgPubType.active}checked="checked"{/if} />
</div>
{assign var='j' value=$j+1}
{foreachelse}
    <div class="z-warningmsg">{gt text='No publication types found.'}</div>
{/foreach}
