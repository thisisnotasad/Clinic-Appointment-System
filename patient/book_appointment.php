<?php 
require_once '../includes/auth.php'; 
require_once '../includes/header.php'; 
require_once '../includes/db_connect.php';

// Only patients can access
if (!isPatient()) {
    header("Location: ../dashboard.php");
    exit();
}
?>

<div class="container mt-4">
    <h2 class="mb-4 text-primary"><i class="fas fa-calendar-plus"></i> Book Appointment</h2>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">

                    <!-- Step 1: Specialization -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Specialization</label>
                        <select id="specialization" class="form-select form-select-lg" required>
                            <option value="">-- Choose Specialization --</option>
                            <?php
                            $result = $conn->query("SELECT * FROM specializations ORDER BY name");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['specialization_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Step 2: Doctor -->
                    <div class="mb-4" id="doctorContainer" style="display:none;">
                        <label class="form-label fw-bold">Select Doctor</label>
                        <select id="doctor" class="form-select form-select-lg" required></select>
                    </div>

                    <!-- Step 3: Date -->
                    <div class="mb-4" id="dateContainer" style="display:none;">
                        <label class="form-label fw-bold">Select Date</label>
                        <input type="date" id="appointmentDate" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Step 4: Time Slots -->
                    <div id="slotsContainer" style="display:none;">
                        <label class="form-label fw-bold">Available Time Slots</label>
                        <div id="timeSlots" class="row g-3"></div>
                    </div>

                    <div id="bookingBtnContainer" class="mt-4" style="display:none;">
                        <button id="bookBtn" class="btn btn-success btn-lg px-5">
                            Confirm Booking
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// All AJAX functions in one clean file later, but inline for now to test fast
document.getElementById('specialization').addEventListener('change', function() {
    const specId = this.value;
    const doctorSelect = document.getElementById('doctor');
    const doctorContainer = document.getElementById('doctorContainer');

    if (!specId) {
        doctorContainer.style.display = 'none';
        return;
    }

    fetch(`ajax_get_doctors.php?specialization_id=${specId}`)
        .then(r => r.json())
        .then(doctors => {
            doctorSelect.innerHTML = '<option value="">-- Select Doctor --</option>';
            doctors.forEach(doc => {
                doctorSelect.innerHTML += `<option value="${doc.doctor_id}">${doc.full_name} (${doc.qualification})</option>`;
            });
            doctorContainer.style.display = 'block';
        });
});

document.getElementById('doctor').addEventListener('change', function() {
    document.getElementById('dateContainer').style.display = this.value ? 'block' : 'none';
});

document.getElementById('appointmentDate').addEventListener('change', function() {
    const doctorId = document.getElementById('doctor').value;
    const date = this.value;
    if (!doctorId || !date) return;

    fetch(`ajax_get_slots.php?doctor_id=${doctorId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('slotsContainer');
            const slotsDiv = document.getElementById('timeSlots');
            slotsDiv.innerHTML = '';


            if (data.on_leave) {
    slotsDiv.innerHTML = '<div class="col-12"><div class="alert alert-danger"><strong>Doctor is on leave</strong> on this date.</div></div>';
} else if (data.slots.length === 0) {
    slotsDiv.innerHTML = '<div class="col-12"><div class="alert alert-warning">No schedule set for this date</div></div>';
} else {
    data.slots.forEach(slot => {
        const isBooked = data.booked.includes(slot);
        slotsDiv.innerHTML += `
            <div class="col-md-3">
                <button class="btn ${isBooked ? 'btn-secondary' : 'btn-outline-primary'} w-100 p-3" 
                        ${isBooked ? 'disabled' : ''} 
                        onclick="selectSlot('${slot}')">
                    ${slot}
                    ${isBooked ? '<br><small>Booked</small>' : ''}
                </button>
            </div>`;
    });
}


            container.style.display = 'block';
            document.getElementById('bookingBtnContainer').style.display = data.slots.length > 0 ? 'block' : 'none';
        });
});

let selectedSlot = '';
function selectSlot(slot) {
    selectedSlot = slot;
    document.querySelectorAll('#timeSlots button').forEach(b => b.classList.remove('btn-primary'));
    event.target.classList.add('btn-primary');
}


// ... (Keep your existing event listeners for specialization, doctor, and date) ...

// Updated Booking Logic
document.getElementById('bookBtn').addEventListener('click', function() {
    const doctorId = document.getElementById('doctor').value;
    const date = document.getElementById('appointmentDate').value;
    const time = selectedSlot;

    // Basic Validation
    if(!doctorId || !date || !time) {
        alert("Please select a doctor, date, and time slot.");
        return;
    }

    const btn = this;
    const originalText = btn.innerHTML;

    // Show loading state
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Booking...';
    btn.disabled = true;

    fetch('process_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `doctor_id=${doctorId}&date=${date}&time=${time}`
    })
    .then(response => {
        // Check if the response is actually JSON
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            // If PHP crashed or outputted raw HTML errors
            throw new Error("Invalid JSON response from server"); 
        }
    })
    .then(res => {
        if (res.success) {
            // SUCCESS: Replace the entire Card Body with a "Beautiful Card"
            const cardBody = document.querySelector('.card-body');
            
            cardBody.innerHTML = `
                <div class="text-center py-4 animate__animated animate__fadeIn">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-success mb-3">Booking Confirmed!</h2>
                    <p class="text-muted mb-4">Thank you. Your appointment has been successfully scheduled.</p>
                    
                    <div class="card bg-light border-0 p-3 mb-4 mx-auto" style="max-width: 400px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-secondary">Doctor:</span>
                            <span>${res.details.doctor}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-secondary">Date:</span>
                            <span>${res.details.date}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-secondary">Time:</span>
                            <span>${res.details.time}</span>
                        </div>
                         <div class="d-flex justify-content-between">
                            <span class="fw-bold text-secondary">Token ID:</span>
                            <span class="text-primary fw-bold">${res.details.token}</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <button onclick="location.reload()" class="btn btn-outline-primary px-4">Book Another</button>
                        <a href="../dashboard.php" class="btn btn-primary px-4">Go to Dashboard</a>
                    </div>
                </div>
            `;
        } else {
            // LOGIC ERROR (e.g. Slot taken)
            alert(res.error || 'Booking failed');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('An unexpected error occurred. Please check your internet or contact support.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});


</script>

<?php require_once '../includes/footer.php'; ?>