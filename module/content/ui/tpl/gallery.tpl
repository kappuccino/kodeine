<script type="text/template" id="view-album">

	<div class="media">
		<div class="icone"><img src="ui/img/gallery-folder.png" /></div>
		<div class="small"><img src="ui/img/gallery-folder.png" /></div>
	</div>

	<div class="action">
		<img class="delete" src="../media/ui/img/media-delete.png" />
		<a href="gallery-album?id_content=<%- id_content %>"><img src="../media/ui/img/media-edit.png"></a>
		<img class="visibility <% if(contentSee == '0'){ %>off<% } %>" src="../media/ui/img/media-eye.png" />


		<% if(!is_alias){ %>
		<img class="alias" src="ui/img/ico-alias.png" />
		<% } %>

		<% if(!gallery.pickMode){ %>
		<img class="poster <% if(!hasPoster){ %>off<% } %>" src="../media/ui/img/media-star.png" />
		<% } %>
	</div>

	<div class="title"><%- contentName %></div>

</script>

<script type="text/template" id="view-item">

	<div class="media">
		<div class="icone loading"></div>
		<div class="small"><img src="ui/img/media-file_file.png" /></div>
	</div>

	<div class="action">
		<img class="delete" src="../media/ui/img/media-delete.png" />
		<a href="gallery-item?id_content=<%- id_content %>"><img src="../media/ui/img/media-edit.png"></a>
		<img class="visibility <% if(contentSee == '0'){ %>off<% } %>" src="../media/ui/img/media-eye.png" />
		<% if(contentItemType == 'image' && !gallery.pickMode){ %>
		<img class="poster <% if(!is_poster){ %>off<% } %>" src="../media/ui/img/media-star.png" />
		<% } %>
	</div>

	<div class="title"><%- contentName %></div>

</script>

<!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<script type="text/template" id="tree-item">

	<div class="item clearfix">
		<span class="toggle"></span>
		<span class="name"><%- contentName %></span>
	</div>
	<ul></ul>

</script>

<script type="text/template" id="path-item">
	<span class="name"><%- contentName %></span>
</script>

<script type="text/template" id="path-sep">
	<span class="name">/</span>
</script>

<!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<div id="modal-upload">
	<form id="uploadembed">
		<?php echo _('Drag & drop files here to upload them.'); ?><br />
		<?php echo _('If your browser do not support this features, click "Browse" button.'); ?>

		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<!-- <a class="btn" href="javascript:$('#file_upload').uploadify('upload')">Envoyer les fichiers</a> -->
		<div id="upqueue" class="clearfix"></div>

		<?php echo _('Remote URL'); ?>.<br />
		<div class="wrapp">
			<textarea id="distantUpload" placeholder="<?php echo _('One URL a line'); ?>"></textarea>
		</div>

		<a class="btn btn-small" id="distantDownload"><?php echo _('Download'); ?></a>
		<a class="btn btn-small" id="buttonCloseUpload"><?php echo _('Cancel'); ?></a>

	</form>
</div>



