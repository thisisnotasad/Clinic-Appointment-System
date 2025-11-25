<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$specialization = $_GET['specialization'] ?? '';
$availability = $_GET['availability'] ?? '';

// Build query with filters
$query = "
    SELECT d.doctor_id, u.full_name, u.email, u.phone, 
           s.name as specialization, s.description as spec_description,
           d.qualification, d.experience_years, d.consultation_fee,
           GROUP_CONCAT(DISTINCT ds.day_of_week) as available_days
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    JOIN specializations s ON d.specialization_id = s.specialization_id
    LEFT JOIN doctor_schedule ds ON d.doctor_id = ds.doctor_id AND ds.is_active = 1
    WHERE 1=1
";

$params = [];
$types = '';

// Add search filter
if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR s.name LIKE ? OR d.qualification LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

// Add specialization filter
if (!empty($specialization)) {
    $query .= " AND s.specialization_id = ?";
    $params[] = $specialization;
    $types .= 'i';
}

$query .= " GROUP BY d.doctor_id, u.full_name, u.email, u.phone, s.name, s.description, d.qualification, d.experience_years, d.consultation_fee";
$query .= " ORDER BY d.experience_years DESC, u.full_name ASC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get specializations for filter dropdown
$specializations = $conn->query("SELECT * FROM specializations ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get total doctor count
$total_doctors = count($doctors);
?>

<style>
.doctors-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.doctors-header {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
    border-left: 4px solid var(--primary);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border-top: 3px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray);
    font-weight: 500;
    font-size: 0.9rem;
}

.filter-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.search-box {
    position: relative;
}

.search-box .form-control {
    padding-left: 3rem;
    border-radius: 8px;
    border: 1px solid var(--gray-light);
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    z-index: 2;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.form-control-custom {
    border: 1px solid var(--gray-light);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: var(--transition);
    background: var(--light);
}

.form-control-custom:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.1);
    background: white;
}

.doctors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.doctor-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 1px solid var(--gray-light);
    overflow: hidden;
}

.doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.doctor-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 1.5rem;
    text-align: center;
    position: relative;
}

.doctor-avatar {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.doctor-body {
    padding: 1.5rem;
}

.specialization-badge {
    background: #e7f3ff;
    color: var(--primary);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1rem;
}

.doctor-info {
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: var(--gray);
    font-size: 0.9rem;
}

.info-item i {
    width: 20px;
    color: var(--primary);
}

.experience-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.fee-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.fee-label {
    color: var(--gray);
    font-size: 0.85rem;
}

.availability {
    background: var(--light);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.availability-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.availability-days {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.day-badge {
    background: white;
    border: 1px solid var(--gray-light);
    color: var(--gray);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.day-badge.available {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.btn-book {
    background: var(--primary);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: white;
    transition: var(--transition);
    width: 100%;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-book:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 108, 247, 0.3);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--gray);
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.action-buttons {
    text-align: center;
    margin-top: 2rem;
}

.btn-outline-custom {
    background: white;
    border: 1px solid var(--gray-light);
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: var(--dark);
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-outline-custom:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(74, 108, 247, 0.05);
}

@media (max-width: 768px) {
    .doctors-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .doctors-header {
        padding: 1.5rem;
    }
}
</style>

<div class="doctors-container">
    <div class="container">
        <!-- Header -->
        <div class="doctors-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">Find Your Doctor</h1>
                    <p class="text-muted mb-0">Browse our team of experienced healthcare professionals</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-light rounded-pill px-3 py-2 d-inline-block">
                        <i class="fas fa-user-md me-2 text-primary"></i>
                        Expert Medical Team
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_doctors ?></div>
                <div class="stat-label">Expert Doctors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($specializations) ?></div>
                <div class="stat-label">Specialties</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Available Support</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">Verified Professionals</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="search-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control form-control-custom" 
                           placeholder="Search doctors by name, specialty, or qualification..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="filter-grid">
                    <div>
                        <label class="form-label fw-semibold">Specialization</label>
                        <select name="specialization" class="form-control form-control-custom">
                            <option value="">All Specialties</option>
                            <?php foreach($specializations as $spec): ?>
                                <option value="<?= $spec['specialization_id'] ?>" 
                                    <?= $specialization == $spec['specialization_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($spec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label fw-semibold">Actions</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom flex-fill">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="find_doctors.php" class="btn btn-outline-custom">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Doctors Grid -->
        <div class="doctors-grid">
            <?php if(count($doctors) > 0): ?>
                <?php foreach($doctors as $doctor): 
                    $available_days = $doctor['available_days'] ? explode(',', $doctor['available_days']) : [];
                    $days_map = ['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat'];
                ?>
                    <div class="doctor-card">
                        <!-- Header -->
                        <div class="doctor-header">
                            <div class="doctor-avatar">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h5 class="mb-1">Dr. <?= htmlspecialchars($doctor['full_name']) ?></h5>
                            <p class="mb-0 opacity-90"><?= htmlspecialchars($doctor['qualification']) ?></p>
                        </div>

                        <!-- Body -->
                        <div class="doctor-body">
                            <div class="specialization-badge">
                                <?= htmlspecialchars($doctor['specialization']) ?>
                            </div>

                            <div class="doctor-info">
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?= htmlspecialchars($doctor['qualification']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?= $doctor['experience_years'] ?> years experience</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?= htmlspecialchars($doctor['phone'] ?? 'Contact via email') ?></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fee-amount">₹<?= number_format($doctor['consultation_fee'], 2) ?></div>
                                    <div class="fee-label">Consultation Fee</div>
                                </div>
                                <span class="experience-badge">
                                    <i class="fas fa-star"></i>
                                    <?= $doctor['experience_years'] ?>+ Years
                                </span>
                            </div>

                            <?php if(!empty($available_days)): ?>
                                <div class="availability">
                                    <div class="availability-title">
                                        <i class="fas fa-calendar-check me-1"></i>Available Days
                                    </div>
                                    <div class="availability-days">
                                        <?php foreach($days_map as $day_num => $day_name): ?>
                                            <span class="day-badge <?= in_array($day_num, $available_days) ? 'available' : '' ?>">
                                                <?= $day_name ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <a href="book_appointment.php?doctor_id=<?= $doctor['doctor_id'] ?>" 
                               class="btn-book mt-3">
                                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-md"></i>
                    <h4>No Doctors Found</h4>
                    <p class="mb-3">We couldn't find any doctors matching your criteria.</p>
                    <a href="find_doctors.php" class="btn btn-custom">
                        <i class="fas fa-redo me-2"></i>View All Doctors
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>