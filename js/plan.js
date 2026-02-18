/* ============================================
   js/plan.js
   Generates fitness plan (calls PHP or
   falls back to client-side demo calculation),
   then renders nutrition cards + schedule
   ============================================ */

/* ====================================================
   MAIN ENTRY — called by "Generate My Plan" button
   ==================================================== */
async function generatePlan() {
    if (!selections.metabolism)   { showToast('Please select your metabolism type.'); return; }
    if (!selections.workoutType)  { showToast('Please select your workout type.');    return; }

    const payload = {
        gender:       selections.gender,
        age:          document.getElementById('age').value,
        weight:       document.getElementById('weight').value,
        weight_unit:  document.getElementById('weightUnit').value,
        height:       document.getElementById('height').value,
        height_unit:  document.getElementById('heightUnit').value,
        experience:   selections.experience,
        goal:         selections.goal,
        metabolism:   selections.metabolism,
        workout_type: selections.workoutType,
    };

    document.getElementById('loading').classList.add('active');

    try {
        const res  = await fetch('php/generate_plan.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        });
        const data = await res.json();
        document.getElementById('loading').classList.remove('active');

        if (data.success) {
            renderResults(data);
        } else {
            showToast(data.message || 'Error generating plan.');
        }

    } catch (e) {
        // ---- Demo / offline fallback ----
        document.getElementById('loading').classList.remove('active');
        renderResults(calculateDemoPlan(payload));
    }
}

/* ====================================================
   CLIENT-SIDE CALCULATION (demo / offline mode)
   ==================================================== */
function calculateDemoPlan(p) {
    // Convert weight to kg
    const wKg = p.weight_unit === 'lbs'
        ? parseFloat(p.weight) * 0.453592
        : parseFloat(p.weight);

    // Convert height to cm
    let hCm = parseFloat(p.height);
    if      (p.height_unit === 'inches') hCm *= 2.54;
    else if (p.height_unit === 'ft')     hCm *= 30.48;

    // Mifflin-St Jeor BMR
    const bmr = p.gender === 'male'
        ? (10 * wKg) + (6.25 * hCm) - (5 * p.age) + 5
        : (10 * wKg) + (6.25 * hCm) - (5 * p.age) - 161;

    // TDEE = BMR × activity multiplier
    const mult  = { beginner: 1.375, intermediate: 1.55, expert: 1.725 };
    let   tdee  = bmr * (mult[p.experience] || 1.55);

    // Metabolism adjustment
    if      (p.metabolism === 'fast') tdee += 250;
    else if (p.metabolism === 'slow') tdee -= 150;

    // Goal adjustment
    if      (p.goal === 'bulking')   tdee += 400;
    else if (p.goal === 'cutting')   tdee -= 500;
    else if (p.goal === 'endurance') tdee += 100;

    const calories = Math.max(1200, Math.round(tdee));
    const protein  = Math.round(wKg * (p.goal === 'cutting' ? 2.2 : p.goal === 'bulking' ? 2.0 : 1.8));
    const fats     = Math.round(calories * 0.28 / 9);
    const carbs    = Math.round((calories - protein * 4 - fats * 9) / 4);

    return {
        success:      true,
        nutrition:    { calories, protein, carbs, fats },
        plan:         buildDemoPlan(p.workout_type, p.experience),
        goal:         p.goal,
        experience:   p.experience,
        workout_type: p.workout_type,
    };
}

/* ====================================================
   DEMO PLAN DATA — used when PHP is unavailable
   ==================================================== */
function buildDemoPlan(type, exp) {
    const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    // Each entry: [ name, focus, [[exercise, sets, reps, rest], ...] ]
    const plans = {
        gym: {
            beginner: [
                ['Upper Body Push', 'Chest, Shoulders, Triceps',   [['Bench Press','3','8-10','90s'],['Shoulder Press','3','10-12','60s'],['Tricep Pushdown','3','12-15','45s']]],
                ['Lower Body',      'Quads, Hamstrings, Glutes',    [['Squat','3','8-10','90s'],['Leg Press','3','10-12','75s'],['Calf Raises','4','15-20','30s']]],
                ['Rest / Cardio',   'Recovery',                     [['Light Walk','1','30 mins','-'],['Stretching','1','20 mins','-']]],
                ['Upper Body Pull', 'Back, Biceps',                 [['Lat Pulldown','3','8-10','90s'],['Seated Row','3','10-12','75s'],['Bicep Curl','3','12-15','45s']]],
                ['Full Body + Core','Compound, Core',               [['Deadlift','3','6-8','120s'],['Plank','3','30-45s','45s'],['Crunches','3','15-20','30s']]],
                ['Cardio',          'Cardio',                       [['Treadmill Run','1','25 mins','-'],['Jump Rope','5','2 mins','60s']]],
                ['Rest Day',        'Full Recovery',                 [['Rest & Recover','-','-','-']]],
            ],
            intermediate: [
                ['Chest + Triceps',  'Chest, Triceps',  [['Bench Press','4','6-8','120s'],['Incline DB Press','4','8-10','90s'],['Skull Crushers','3','10-12','60s']]],
                ['Back + Biceps',    'Back, Biceps',    [['Deadlift','4','5-6','180s'],['Pull-Ups','4','8-10','90s'],['Barbell Curl','4','8-10','60s']]],
                ['Legs + Core',      'Legs, Core',      [['Barbell Squat','5','5','180s'],['Leg Press','4','10-12','90s'],['Leg Curl','4','10-12','60s']]],
                ['Rest / Recovery',  'Recovery',        [['Foam Rolling','1','15 mins','-'],['Mobility','1','20 mins','-']]],
                ['Shoulders + Arms', 'Shoulders, Arms', [['OHP Press','4','6-8','120s'],['Arnold Press','3','10-12','75s'],['Lateral Raises','4','12-15','45s']]],
                ['Full Body Power',  'Compound',        [['Power Cleans','4','4-6','120s'],['Front Squat','3','6-8','120s'],['Farmer Carries','4','30m','60s']]],
                ['Rest Day',         'Full Recovery',   [['Complete Rest','-','-','-']]],
            ],
            expert: [
                ['Heavy Chest',      'Chest, Power',    [['Paused Bench','5','3-5','180s'],['Weighted Dips','4','6-8','120s'],['Cable Crossover','3','12-15','60s']]],
                ['Squat Focus',      'Legs',            [['Competition Squat','6','2-4','240s'],['Front Squat','4','4-6','180s'],['Hack Squat','3','8-10','90s']]],
                ['Pull (Heavy)',     'Back, Biceps',    [['Sumo Deadlift','5','3-5','240s'],['Weighted Pull-Ups','4','6-8','120s'],['EZ Bar 21s','4','21','60s']]],
                ['Recovery',        'Mobility',         [['Yoga','1','30 mins','-'],['Light Cardio','1','20 mins','-']]],
                ['Shoulders + Arms','Shoulders, Arms',  [['Push Press','5','4-5','180s'],['Arnold Press','4','8-10','90s'],['Drag Curl','4','10-12','60s']]],
                ['Olympic / Conditioning','Power, Cardio',[['Power Clean & Jerk','5','3','180s'],['Box Jumps','4','6','90s'],['Battle Ropes','5','30s','30s']]],
                ['Rest Day',        'Full Recovery',    [['Complete Rest','-','-','-']]],
            ],
        },
        home: {
            beginner: [
                ['Upper Body Push',  'Chest, Shoulders',  [['Push-Ups','3','8-12','60s'],['Pike Push-Ups','3','8-10','60s'],['Tricep Dips (Chair)','3','10-12','45s']]],
                ['Lower Body',       'Legs, Glutes',      [['Bodyweight Squats','3','15-20','45s'],['Glute Bridges','3','15-20','30s'],['Lunges','3','10 each','45s']]],
                ['Rest / Light Cardio','Recovery',        [['Brisk Walk','1','30 mins','-'],['Stretching','1','15 mins','-']]],
                ['Core + Pull',      'Back, Core',        [['Plank','3','30-45s','45s'],['Superman Hold','3','10-12','45s'],['Bicycle Crunches','3','15-20','30s']]],
                ['Full Body Circuit','Full Body',         [['Burpees','3','8-10','60s'],['Jump Squats','3','10-12','45s'],['Mountain Climbers','3','30s','30s']]],
                ['Cardio + Flex',    'Cardio',            [['Running','1','20-30 mins','-'],['Yoga','1','20 mins','-']]],
                ['Rest Day',         'Full Recovery',     [['Rest & Recover','-','-','-']]],
            ],
            intermediate: [
                ['Push Power',       'Chest, Shoulders',  [['Archer Push-Ups','4','8-10 each','75s'],['Handstand Push-Up Prog.','4','5-8','90s'],['Ring Dips','3','8-10','75s']]],
                ['Legs + Plyo',      'Legs, Power',       [['Pistol Squat Prog.','4','5-8 each','90s'],['Jump Squats','4','12-15','60s'],['Nordic Curls','3','6-10','75s']]],
                ['Active Recovery',  'Recovery',          [['Yoga Flow','1','30 mins','-'],['Foam Rolling','1','15 mins','-']]],
                ['Pull + Core',      'Back, Biceps, Core',[['Pull-Ups','4','8-12','90s'],['Chin-Ups','3','8-10','90s'],['L-Sit Hold','3','10-20s','45s']]],
                ['Full Body HIIT',   'Conditioning',      [['Burpee Pull-Ups','4','6-8','75s'],['Plyometric Push-Ups','4','8-10','75s'],['Tuck Jumps','3','10','45s']]],
                ['Endurance + Skills','Cardio, Skills',   [['Handstand Practice','5','20-30s','-'],['Running / Cycling','1','30-45 mins','-']]],
                ['Rest Day',         'Full Recovery',     [['Complete Rest','-','-','-']]],
            ],
            expert: [
                ['Strength Calisthenics','Planche, Rings',[['Planche Push-Up Prog.','5','3-6','180s'],['Ring Dips','4','8-10','120s'],['One-Arm Push-Up Neg.','4','3-5 each','120s']]],
                ['Leg Power',           'Legs, Explosiveness',[['Pistol Squats','5','8-10 each','90s'],['Shrimp Squats','4','6-8 each','75s'],['Depth Jumps','4','6','90s']]],
                ['Front Lever + Pull',  'Back, Core Skills',[['Front Lever Prog.','5','5-10s hold','180s'],['One-Arm Pull-Up Neg.','4','3-5 each','150s'],['Dragon Flag','3','5-8','90s']]],
                ['Recovery + Mobility', 'Recovery',       [['Active Recovery Yoga','1','45 mins','-']]],
                ['Muscle-Up + Skills',  'Full Body Skills',[['Muscle-Ups','5','5-8','180s'],['Ring Muscle-Ups','3','3-5','180s'],['Typewriter Pull-Ups','3','5-8','90s']]],
                ['HIIT + Conditioning', 'Conditioning',   [['Sprint Intervals','8','30s/30s','-'],['Burpee Pull-Ups','5','10','60s'],['Jump Rope (Double Under)','5','50 reps','30s']]],
                ['Rest Day',            'Full Recovery',  [['Complete Rest','-','-','-']]],
            ],
        }
    };

    const expPlans = plans[type]?.[exp] || plans.home.beginner;
    const plan = {};

    ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'].forEach((day, i) => {
        const p = expPlans[i] || expPlans[0];
        plan[day] = {
            name:      p[0],
            focus:     p[1],
            exercises: p[2].map(ex => ({ name: ex[0], sets: ex[1], reps: ex[2], rest: ex[3] }))
        };
    });

    return plan;
}

/* ====================================================
   RENDER RESULTS PAGE
   ==================================================== */
function renderResults(data) {
    document.getElementById('formSection').style.display = 'none';

    const resultsEl = document.getElementById('results');
    resultsEl.classList.add('active');

    // -- Goal / exp / type tags
    const goalLabels = { bulking: 'Bulking', cutting: 'Cutting', endurance: 'Endurance', general_fitness: 'General Fitness' };
    document.getElementById('resultsTags').innerHTML = `
        <span class="tag tag-goal">${goalLabels[data.goal] || data.goal}</span>
        <span class="tag tag-exp">${data.experience}</span>
        <span class="tag tag-type">${data.workout_type === 'gym' ? 'Gym' : 'Home'} Workout</span>
    `;

    // -- Nutrition cards
    const n = data.nutrition;
    const nutItems = [
        { label: 'Calories', value: n.calories, unit: 'kcal' },
        { label: 'Protein',  value: n.protein,  unit: 'g' },
        { label: 'Carbs',    value: n.carbs,     unit: 'g' },
        { label: 'Fats',     value: n.fats,      unit: 'g' },
    ];
    document.getElementById('nutritionGrid').innerHTML = nutItems.map(item => `
        <div class="nutrition-card">
            <div class="nut-value">${item.value}</div>
            <div class="nut-unit">${item.unit}</div>
            <div class="nut-label">${item.label}</div>
        </div>
    `).join('');

    // -- Weekly workout schedule
    document.getElementById('scheduleGrid').innerHTML = Object.entries(data.plan).map(([day, info]) => `
        <div class="day-card" id="day-${day}">
            <div class="day-header" onclick="toggleDay('${day}')">
                <div class="day-left">
                    <div class="day-name">${day}</div>
                    <div class="day-badge">${info.name}</div>
                </div>
                <div style="display:flex;align-items:center;gap:16px">
                    <div class="day-focus">${info.focus}</div>
                    <div class="day-chevron">▾</div>
                </div>
            </div>
            <div class="day-exercises">
                <table class="exercise-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Exercise</th>
                            <th>Sets</th>
                            <th>Reps</th>
                            <th>Rest</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${info.exercises.map((ex, i) => `
                            <tr>
                                <td style="color:var(--muted);font-family:'DM Mono',monospace;font-size:0.75rem">${String(i+1).padStart(2,'0')}</td>
                                <td class="exercise-name">${ex.name}</td>
                                <td><span class="badge-sets">${ex.sets}</span></td>
                                <td style="font-family:'DM Mono',monospace;font-size:0.85rem">${ex.reps}</td>
                                <td style="color:var(--muted);font-family:'DM Mono',monospace;font-size:0.8rem">${ex.rest}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `).join('');

    // Auto-open Monday
    toggleDay('Monday');
    resultsEl.scrollIntoView({ behavior: 'smooth' });
}

/* ====================================================
   SCHEDULE ACCORDION
   ==================================================== */
function toggleDay(day) {
    document.getElementById('day-' + day).classList.toggle('open');
}

/* ====================================================
   REBUILD PLAN
   ==================================================== */
function restartForm() {
    document.getElementById('results').classList.remove('active');
    document.getElementById('formSection').style.display = 'block';
    resetForm();
    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
}
