
{foreach from=$comments item=comment}
	<h1>{$comment.comment}</h1>
{/foreach}

<div class="alert alert-info" role="alert">
  <i class="material-icons">Info</i><p class="alert-text">{$message}</p>
</div>

<form action="//{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" method="POST">

	<fieldset class="form-group">
		<label class="form-control-label" for="name">Name</label>
		<input type="text" class="form-control" name="name">
	</fieldset>
	
	<fieldset class="form-group">
		<label class="form-control-label" for="comment">Comment</label>
		<textarea class="form-control" name="comment"></textarea>
	</fieldset>

	<button type="submit" class="btn btn-primary">Save</button>

</form>
