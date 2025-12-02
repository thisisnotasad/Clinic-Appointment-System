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

                    <div class="mb-4" id="doctorContainer" style="display:none;">
                        <label class="form-label fw-bold">Select Doctor</label>
                        <select id="doctor" class="form-select form-select-lg" required></select>
                    </div>

                    <div class="mb-4" id="feeContainer" style="display:none;">
                        <label class="form-label fw-bold">Consultation Fee</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0 text-muted">$</span>
                            <input type="text" id="consultationFee" class="form-control bg-light border-start-0 fw-bold text-dark" disabled readonly>
                        </div>
                    </div>

                    <div class="mb-4" id="dateContainer" style="display:none;">
                        <label class="form-label fw-bold">Select Date</label>
                        <input type="date" id="appointmentDate" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>">
                    </div>

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
document.getElementById('specialization').addEventListener('change', function() {
    const specId = this.value;
    const doctorSelect = document.getElementById('doctor');
    const doctorContainer = document.getElementById('doctorContainer');
    // Hide subsequent fields if specialization changes
    document.getElementById('feeContainer').style.display = 'none';
    document.getElementById('dateContainer').style.display = 'none';
    document.getElementById('slotsContainer').style.display = 'none';
    document.getElementById('bookingBtnContainer').style.display = 'none';

    if (!specId) {
        doctorContainer.style.display = 'none';
        return;
    }

    fetch(`ajax_get_doctors.php?specialization_id=${specId}`)
        .then(r => r.json())
        .then(doctors => {
            doctorSelect.innerHTML = '<option value="">-- Select Doctor --</option>';
            doctors.forEach(doc => {
                // Added data-fee attribute to store the fee in the option element
                doctorSelect.innerHTML += `<option value="${doc.doctor_id}" data-fee="${doc.consultation_fee}">${doc.full_name} (${doc.qualification})</option>`;
            });
            doctorContainer.style.display = 'block';
        });
});

document.getElementById('doctor').addEventListener('change', function() {
    const feeContainer = document.getElementById('feeContainer');
    const dateContainer = document.getElementById('dateContainer');
    const feeInput = document.getElementById('consultationFee');
    
    // Get the selected option to access the data-fee attribute
    const selectedOption = this.options[this.selectedIndex];
    const fee = selectedOption.getAttribute('data-fee');

    if (this.value && fee) {
        // Show fee
        feeInput.value = fee; 
        feeContainer.style.display = 'block';
        dateContainer.style.display = 'block';
    } else {
        feeContainer.style.display = 'none';
        dateContainer.style.display = 'none';
        document.getElementById('slotsContainer').style.display = 'none';
        document.getElementById('bookingBtnContainer').style.display = 'none';
    }
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
                    // Add data-booked attribute and pass the element to selectSlot
                    slotsDiv.innerHTML += `
                        <div class="col-md-3">
                            <button type="button" data-booked="${isBooked ? 'true' : 'false'}" class="btn ${isBooked ? 'btn-secondary' : 'btn-outline-primary'} w-100 p-3" 
                                    ${isBooked ? 'disabled' : ''} 
                                    onclick="selectSlot(this, '${slot}')">
                                <span class="slot-label">${slot}</span>
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
function selectSlot(el, slot) {
    selectedSlot = slot;

    // Normalize classes for all buttons based on booked state
    document.querySelectorAll('#timeSlots button').forEach(b => {
        const booked = b.getAttribute('data-booked') === 'true';
        b.classList.remove('btn-primary', 'text-white');
        if (booked) {
            b.classList.remove('btn-outline-primary');
            b.classList.add('btn-secondary');
        } else {
            b.classList.remove('btn-secondary');
            b.classList.add('btn-outline-primary');
        }
    });

    // If clicked button is not booked, mark it as selected (blue with readable text)
    if (el.getAttribute('data-booked') !== 'true') {
        el.classList.remove('btn-outline-primary');
        el.classList.add('btn-primary', 'text-white');
    }
}

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
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            throw new Error("Invalid JSON response from server"); 
        }
    })
    .then(res => {
        if (res.success) {
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