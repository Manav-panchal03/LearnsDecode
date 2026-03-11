<?php
include 'includes/header.php';
// Handle category actions
if(isset($_POST['action'])){
    if($_POST['action'] == 'add'){
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        mysqli_query($conn, "INSERT INTO categories (name, icon) VALUES ('$name', '$icon')");
        $message = "Category added successfully!";
    } elseif($_POST['action'] == 'edit' && isset($_POST['category_id'])){
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        mysqli_query($conn, "UPDATE categories SET name='$name', icon='$icon' WHERE id='$category_id'");
        $message = "Category updated successfully!";
    } elseif($_POST['action'] == 'delete' && isset($_POST['category_id'])){
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        mysqli_query($conn, "DELETE FROM categories WHERE id='$category_id'");
        $message = "Category deleted successfully!";
    }
}

// Get all categories with course count
$categories_query = "SELECT c.*, COUNT(co.id) as course_count FROM categories c LEFT JOIN courses co ON c.id = co.category_id GROUP BY c.id ORDER BY c.created_at DESC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card { border-radius: 15px; border: none; }
    .table thead { border-radius: 15px 15px 0 0; }
    .category-icon { transition: transform 0.3s ease; }
    tr:hover .category-icon { transform: scale(1.3) rotate(10deg); }
    .badge { padding: 8px 12px; border-radius: 50px; }
    .modal-content { border-radius: 20px; border: none; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <h2 class="fw-bold"><i class="fas fa-tags me-2 text-warning"></i>Manage Categories</h2>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>

    <?php if(isset($message)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $message; ?>',
                timer: 2500,
                showConfirmButton: false,
                showClass: { popup: 'animate__animated animate__fadeInRight' }
            });
        </script>
    <?php endif; ?>

    <div class="card shadow animate__animated animate__fadeInUp">
        <div class="card-header bg-warning text-dark p-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Course Categories</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Icon</th>
                            <th>Category Name</th>
                            <th>Linked Courses</th>
                            <th>Created On</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $delay = 0.1;
                        while($category = mysqli_fetch_assoc($categories_result)): 
                        ?>
                            <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                <td class="ps-4">
                                    <div class="category-icon d-inline-block">
                                        <i class="<?php echo htmlspecialchars($category['icon']); ?> fa-2x text-primary"></i>
                                    </div>
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($category['name']); ?></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info">
                                        <i class="fas fa-book-open me-1"></i> <?php echo $category['course_count']; ?> Courses
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary me-1 rounded-pill px-3" 
                                            onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['icon'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    
                                    <form id="delete-form-<?php echo $category['id']; ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                onclick="confirmDelete(<?php echo $category['id']; ?>)">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                        $delay += 0.05;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg animate__animated animate__zoomIn">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Web Development" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Class (FontAwesome)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-icons text-muted"></i></span>
                            <input type="text" name="icon" class="form-control rounded-end-3" placeholder="fas fa-code" required>
                        </div>
                        <div class="form-text mt-2 small">Need icons? Use classes from <a href="https://fontawesome.com/icons" target="_blank">FontAwesome</a></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="add" class="btn btn-primary rounded-pill px-4">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg animate__animated animate__zoomIn">
            <div class="modal-header bg-warning border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Update Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" name="name" id="edit_category_name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Class (FontAwesome)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-icons text-muted"></i></span>
                            <input type="text" name="icon" id="edit_category_icon" class="form-control rounded-end-3" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="edit" class="btn btn-warning rounded-pill px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, icon) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_category_icon').value = icon;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Category?',
        text: "This category and its connection to courses will be removed!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6e7881',
        confirmButtonText: 'Yes, Delete it!',
        showClass: { popup: 'animate__animated animate__headShake' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>