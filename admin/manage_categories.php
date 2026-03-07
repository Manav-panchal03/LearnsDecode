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

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags me-2"></i>Manage Categories</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>

            <?php if(isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Course Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-3d">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Courses</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <tr>
                                        <td><i class="<?php echo htmlspecialchars($category['icon']); ?> fa-2x text-primary"></i></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $category['course_count']; ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['icon']); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class (FontAwesome)</label>
                        <input type="text" name="icon" class="form-control" placeholder="fas fa-code" required>
                        <div class="form-text">Example: fas fa-code, fas fa-mobile-alt, etc.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="add" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" id="edit_category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class (FontAwesome)</label>
                        <input type="text" name="icon" id="edit_category_icon" class="form-control" required>
                        <div class="form-text">Example: fas fa-code, fas fa-mobile-alt, etc.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="edit" class="btn btn-primary">Update Category</button>
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
</script>

<?php include 'includes/footer.php'; ?>