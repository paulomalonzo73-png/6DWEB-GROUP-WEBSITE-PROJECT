/* ============================================
   js/form.js
   Multi-step form: navigation, validation,
   option card selection, reset
   ============================================ */

let currentStep = 1;
const selections = {};   // stores all user choices { gender, experience, goal, metabolism, workoutType }

/* ---------- OPTION CARD SELECTION ---------- */
function selectOpt(el, group, value) {
    // Deselect all cards in the same group
    document.querySelectorAll(`[onclick*="'${group}'"]`).forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selections[group] = value;
}

/* ---------- MOVE FORWARD ---------- */
function nextStep(from) {
    if (!validateStep(from)) return;

    document.getElementById('step' + from).classList.remove('active');
    currentStep = from + 1;
    document.getElementById('step' + currentStep).classList.add('active');

    updateDots();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ---------- MOVE BACKWARD ---------- */
function prevStep(from) {
    document.getElementById('step' + from).classList.remove('active');
    currentStep = from - 1;
    document.getElementById('step' + currentStep).classList.add('active');
    updateDots();
}

/* ---------- VALIDATION ---------- */
function validateStep(step) {
    if (step === 1) {
        if (!selections.gender) {
            showToast('Please select your gender.');
            return false;
        }
        const age    = document.getElementById('age').value;
        const weight = document.getElementById('weight').value;
        const height = document.getElementById('height').value;

        if (!age || !weight || !height) {
            showToast('Please fill in all body stats.');
            return false;
        }
        if (age < 13 || age > 80) {
            showToast('Please enter a valid age (13–80).');
            return false;
        }
    }
    if (step === 2) {
        if (!selections.experience) {
            showToast('Please select your experience level.');
            return false;
        }
        if (!selections.goal) {
            showToast('Please select your goal.');
            return false;
        }
    }
    return true;
}

/* ---------- STEP PROGRESS DOTS ---------- */
function updateDots() {
    document.querySelectorAll('.step-dot').forEach((dot, i) => {
        dot.className = 'step-dot';
        if      (i + 1 < currentStep)  dot.classList.add('done');
        else if (i + 1 === currentStep) dot.classList.add('active');
    });
}

/* ---------- RESET FORM ---------- */
function resetForm() {
    currentStep = 1;

    // Clear selections object
    Object.keys(selections).forEach(k => delete selections[k]);

    // Deselect all option cards
    document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));

    // Clear number inputs
    ['age', 'weight', 'height'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Show step 1, hide others
    document.querySelectorAll('.step-form').forEach(s => s.classList.remove('active'));
    document.getElementById('step1').classList.add('active');

    updateDots();
}

/* ---------- TOAST UTILITY ---------- */
function showToast(msg) {
    const t = document.createElement('div');
    t.className   = 'toast';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
