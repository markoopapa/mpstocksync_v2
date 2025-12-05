{if isset($sql) && $sql}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-filter"></i> Filters
        <span class="badge">{$list_total}</span>
    </div>
    <div class="panel-body">
        {$sql}
    </div>
</div>
{/if}

{if isset($errors) && $errors}
<div class="alert alert-danger">
    <ul>
        {foreach from=$errors item=error}
            <li>{$error}</li>
        {/foreach}
    </ul>
</div>
{/if}

{if isset($confirmations) && $confirmations}
<div class="alert alert-success">
    <ul>
        {foreach from=$confirmations item=confirmation}
            <li>{$confirmation}</li>
        {/foreach}
    </ul>
</div>
{/if}
