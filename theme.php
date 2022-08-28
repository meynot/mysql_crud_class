<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<!-- <?php echo 'action=>'.$action ?> -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mysql Single file CRUD</title>
	<base href="<?php echo $url ?>">
	<meta name="csrf-token" content="<?php echo $crud->getCSRFToken() ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
	<style>
	.table .tbody TR TD { padding: 0px; }
	</style>
  </head>
  <body>
   <div class="container">
    <div class="my-3">
	 <div class="card">
	  <div class="card-header"><a class="btn btn-link" href=".">MySQL CRUD</a></div>
	  <div class="card-body">
	  
	    <table id="rowsTable" class="table table-striped table-hover">
		  <thead>
		    <tr><?php foreach($settings['viewable'] as $col) echo '<th>'.$col.'</th>'; ?></tr>
		  </thead>
		  <tbody>
		   <?php if( $result['data'] ): ?>
		   <?php foreach($result['data'] as $row): ?>
		   <tr role="button" id="row_<?php echo $row['id'] ?>"><?php foreach($settings['viewable'] as $col) echo '<td>'.$row[$col].'</td>'; ?></tr>
		   <?php endforeach; ?>
		   <?php else: ?>
		    <tr><td class="text-center" colspan="<?php echo count($settings['viewable']) ?>">hello world</td></tr>
		   <?php endif; ?>
		  </tbody>
		</table>
	    
	  </div>
	  <div class="card-footer">
		Copyright &copy;
	  </div>
	 </div><!-- ./card -->
	</div><!-- padding -->
	
<!-- Modal -->
<div class="modal fade" id="crudModal" data-row="" tabindex="-1" aria-labelledby="crudModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="crudModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
	  <form id="crudModalForm" method="POST" action="<?php echo $url ?>">
	   <input type="hidden" name="_method" value="post" />
	   <input type="hidden" name="_token" value="" />
	   <input type="hidden" name="q" value="" />
	   <input type="hidden" name="rowid" value="" />
      <div id="crudModalBody" class="modal-body"></div>
	  <div class="small text-center p-0 m-0"><sup class="text-danger d-block">All columns are treated as text</sup></div>
      <div class="modal-footer">
	   <div class="d-flex justify-content-between w-100">
	    <div class="move-buttons"><button type="button" class="btn btn-info btn-previous"><</button> <button type="button" class="btn btn-info btn-next">></button></div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning btn-edit">Edit</button>
        <button type="button" class="btn btn-danger btn-delete">Delete</button>
        <button type="submit" role="button" class="btn btn-primary btn-save d-none">Save changes</button>
	   </div>
      </div>
	  </form>
    </div>
  </div>
</div>

	
   </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>	
   <script>
(function() {

   const cols = [<?php foreach($settings['fillable'] as $col) echo "'$col',"; ?>];
   const base = document.querySelector('base').href;
   const bsModal = new bootstrap.Modal('#crudModal');
   const modal = document.getElementById('crudModal');
   const csrf = document.querySelector('meta[name="csrf-token"]').content;

   const dataTable = new simpleDatatables.DataTable('#rowsTable', {
      searchable: true,
      fixedHeight: true,
	});

	addCreateButton();
	
	let is_form_created=false;
	
	document.querySelectorAll('#rowsTable TBODY TR TD').forEach(td=>{
		td.addEventListener('click', function(event) {
			const id = this.closest('TR').id.substring(4);
			const fd= new FormData();
			
			fd.append('_token', csrf);
			fd.append('q', 'show');
			fd.append('id', id);
			fd.append('_method', 'post');
			
			ajaxRequest(base, function(res) {
				const row = JSON.parse(res);
				showRow('View Row', '', row.data);
				bsModal.show(bsModal);
			}, 'POST', fd);
		});
	});
	
	document.querySelector('.btn-create')
	  .addEventListener('click', 
	    function(event) {
			showOrCreateForm('Create new row', 'store');			
	    });
	// Buttons in Modal previous, next, edit, delete and save[submit]
	modal.querySelector('.btn-previous')
	  .addEventListener('click', function(event) {
		  if ( modal.dataset.row < 2 ) return false;
			const fd= new FormData();
			
			fd.append('_token', csrf);
			fd.append('q', 'previous');
			fd.append('id', modal.dataset.row);
			fd.append('_method', 'post');
			
			ajaxRequest(base, function(res) {
				const row = JSON.parse(res);
				showRow('View Row', '', row.data);
				bsModal.show(bsModal);
			}, 'POST', fd);
		});

	modal.querySelector('.btn-next')
	  .addEventListener('click', function(event) {
		  if ( modal.dataset.row < 2 ) return false;
			const fd= new FormData();
			
			fd.append('_token', csrf);
			fd.append('q', 'next');
			fd.append('id', modal.dataset.row);
			fd.append('_method', 'post');
			
			ajaxRequest(base, function(res) {
				const row = JSON.parse(res);
				showRow('View Row', '', row.data);
				bsModal.show(bsModal);
			}, 'POST', fd);
		});

	modal.querySelector('.btn-edit')
	  .addEventListener('click', 
	    function(event) {
			showButtons(false);
			const fd= new FormData();
			
			fd.append('_token', csrf);
			fd.append('q', 'edit');
			fd.append('id', modal.dataset.row);
			fd.append('_method', 'post');
			
			ajaxRequest(base, function(res) {
				const row = JSON.parse(res);
				showOrCreateForm('Edit Row', 'update', row.data);
				
			}, 'POST', fd);
	    });
		
	modal.querySelector('.btn-delete')
	  .addEventListener('click', 
	    function(event) {
			const fd= new FormData();
			
			fd.append('_token', csrf);
			fd.append('q', 'edit');
			fd.append('id', modal.dataset.row);
			fd.append('_method', 'post');
			
			ajaxRequest(base, function(res) {
				const row = JSON.parse(res);
				showOrCreateForm('Edit Row', 'update', row.data);
				
			}, 'POST', fd);
	    });
	
	// Functions
	/************************************
	 * ajaxRequest	XMLHttpRequest request
	 * Arguments 
	 *  url which is the path/url of data to be requested
	 *  fnResult is a function that will handle response object
	 *  method is request method [POST/GET] but in this we only use POST
	 *  data is which to be sent in request
	 ************************************/
	function ajaxRequest(url, fnResult, method, data) {
		//console.log(url, method, data);
		// 1. Create a new XMLHttpRequest object
		const xhr = new XMLHttpRequest();
		
		// 2. Set XMLHttpRequest method
		xhr.open(method, url, true);
		xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");

		// 3. Send the request over the network
		xhr.send(data);

		// 4. This will be called after the response is received	
		xhr.onload = function(event) {
			if (xhr.status != 200) { // analyze HTTP status of the response
				console.log('Error ${xhr.status}: ${xhr.statusText}'); // e.g. 404: Not Found
			} else { // show the result
				//console.log('Done, got ${xhr.response.length} bytes'); // response is the server response
				//console.log( 'Done, got ' + xhr.response );
				let responseObj = JSON.parse(xhr.response);
				//let responseObj = JSON.parse(xhr.responseText);
				//console.log(typeof responseObj);
				fnResult(responseObj);
			}
		};
		
		// 5. Progress process
		xhr.onprogress = function(event) {
			if (event.lengthComputable) {
				console.log('Received ' + event.loaded + ' of ' + event.total + ' bytes');
			} else {
				console.log('Received ' + event.loaded + ' bytes'); // no Content-Length
			}
		};
		
		// 6. Are there any errors!
		xhr.onerror = function(event) {
			console.log('Request error');
		};
		
		// 7. Did request aborted!
		xhr.onabort = function(event) {
			console.log('Request aborted');
		};
	}
	
	/************************************
	 * addCreateButton
	 * Add [Create] button to dataTable-top DIV which is at top of TABLE element
	 ************************************/
	function addCreateButton() {
		const dataTableTop = document.querySelector('.dataTable-top');
		const div = document.createElement('div');
		div.className='dataTable-buttons text-end';
		const btn = document.createElement('button');
			btn.id='btn-create';
			btn.className='btn btn-success btn-create';
			btn.type='button';
			btn.dataset.bsToggle='modal';
			btn.dataset.bsTarget='#crudModal';
			btn.role='button';
			btn.innerHTML = 'Create';
		
		//dataTableTop.insertBefore(btn, dataTableTop.firstChild);
		div.appendChild(btn);
		dataTableTop.appendChild(div);
		dataTableTop.className='d-flex justify-content-between dataTable-top';
	}

	/************************************
	 * showOrCreateForm will create form elements on modal/container
	 * arguments 
	 *  title is modal title
	 *  query which to be sent by request
	 *  data contains column names [fillable]
	 ***********************************/
	function showOrCreateForm(title, query, data)  	{
		if( title == undefined || query == undefined ) return false;
		
		modal.querySelector('#crudModalLabel').innerHTML = title;
		modal.querySelector('input[name=_token]').value=csrf;
		modal.querySelector('input[name=q]').value = query;
		const modelBody = modal.querySelector('#crudModalBody');
		// if not create then data will have row id!
		if( data != undefined  ) 
			modal.querySelector('input[name=rowid]').value = data['id'];
		showButtons(false);
		modelBody.innerHTML='';
		for(var i=0;i<cols.length;i++)
		{
		  let div = document.createElement('div');
			div.className='form-floating mb-1';
			modelBody.appendChild(div);
			
		  let tag = document.createElement('input');
			tag.type = 'text';
			tag.id = 'i'+cols[i];
			tag.name = cols[i];
			tag.className = 'form-control';
			tag.placeholder=cols[i];
			if( data != undefined  ) tag.value = data[cols[i]];
			div.appendChild(tag);
		
		  let label = document.createElement('label');
			label.for='i'+cols[i];
			label.innerText = cols[i];
			div.appendChild(label);
			
		if( cols[i] === 'id' ) div.setAttribute('style', 'pointer-events: none;');
		}
	}
	
	/************************************
	 * showRow will create TABLE elements on modal/container to show columns
	 * arguments 
	 *  title is modal title
	 *  query which to be sent by request
	 *  data contains column names [fillable]
	 ***********************************/
	function showRow(title, query, data)  	{
		if( title == undefined  ) return false;
		const modelBody = modal.querySelector('#crudModalBody');
		// assign title to modal
		modal.querySelector('#crudModalLabel').innerHTML = title;
		// add attribnute data-row to edit and delete buttons
		modal.setAttribute('data-row', data.id);
		showButtons(true);

		let result = '<table class="table"><tbody>';
		for (var k in data) 
			result+='<tr><th>' + k + '</th><td>' + data[k] + '</td></tr>';
		result+='</tbody></table>';
		// set modal body;
		modelBody.innerHTML = result;
	}

	/************************************
	 * showButtons to hide/show buttons on modal
	 * arguments 
	 *  display is boolean when false show all button except [Save and Cancel] 
	 ***********************************/
	function showButtons(display) {
		if (display==true) {
			modal.querySelector('.btn-edit').classList.remove('d-none');
			modal.querySelector('.btn-delete').classList.remove('d-none');
			modal.querySelector('.move-buttons').classList.remove('d-none');
			modal.querySelector('.btn-save').classList.add('d-none');
		} else {
			modal.querySelector('.btn-edit').classList.add('d-none');
			modal.querySelector('.btn-delete').classList.add('d-none');
			modal.querySelector('.move-buttons').classList.add('d-none');
			modal.querySelector('.btn-save').classList.remove('d-none');
		}
	}

})();
   </script>
  </body>
</html>