<!-- Modal -->
<div class="modal" id="addShortcut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Shortcut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-url-browser">
                    <div class="mb-3">
                        <!-- <div class="alert alert-warning" role="alert">
                            A simple warning alert—check it out!
                        </div>  -->
                        <input type="hidden" name="user-email-log" id="user-email-log"> 
                        <input type="hidden" name="user-id-log" id="user-id-log">
                        <label for="exampleInputPassword1" class="form-label">URL</label>
                        <input type="text" class="form-control" name="browserUrl" id="browserUrl"> 
                        <label for="exampleInputPassword1" class="form-label">Name</label>
                        <input type="text" class="form-control" name="browserName" id="browserName">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Done</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div> 

<!-- new user for adding password -->
<div class="modal" id="new-user-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Shortcut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="new-user-login">
                    <div class="mb-3">
                        <label class="form-label">Enter new Password:</label>
                        <input type="text" class="form-control" name="password" id="password">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Done</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>