<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$gender = $data['gender'] ?? 'male';
$age = (int)($data['age'] ?? 25);
$weight = (float)($data['weight'] ?? 70);
$weight_unit = $data['weight_unit'] ?? 'kg';
$height = (float)($data['height'] ?? 170);
$height_unit = $data['height_unit'] ?? 'cm';
$experience = $data['experience'] ?? 'beginner';
$goal = $data['goal'] ?? 'general_fitness';
$metabolism = $data['metabolism'] ?? 'moderate';
$workout_type = $data['workout_type'] ?? 'gym';

// Convert weight to kg
$weight_kg = $weight;
if ($weight_unit === 'lbs') {
    $weight_kg = $weight * 0.453592;
}

// Convert height to cm
$height_cm = $height;
if ($height_unit === 'inches') {
    $height_cm = $height * 2.54;
} elseif ($height_unit === 'ft') {
    $height_cm = $height * 30.48;
}

// Calculate BMR (Mifflin-St Jeor)
if ($gender === 'male') {
    $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + 5;
} else {
    $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) - 161;
}

// Activity multiplier based on experience
$multipliers = ['beginner' => 1.375, 'intermediate' => 1.55, 'expert' => 1.725];
$tdee = $bmr * ($multipliers[$experience] ?? 1.55);

// Metabolism adjustment
if ($metabolism === 'fast') {
    $tdee += 250;
} elseif ($metabolism === 'slow') {
    $tdee -= 150;
}

// Goal-based calorie adjustment
$calories = $tdee;
if ($goal === 'bulking') {
    $calories += 400;
} elseif ($goal === 'cutting') {
    $calories -= 500;
} elseif ($goal === 'endurance') {
    $calories += 100;
}
$calories = max(1200, round($calories));

// Macro calculation
if ($goal === 'bulking') {
    $protein = round($weight_kg * 2.0);
    $fats = round($calories * 0.25 / 9);
    $carbs = round(($calories - ($protein * 4) - ($fats * 9)) / 4);
} elseif ($goal === 'cutting') {
    $protein = round($weight_kg * 2.2);
    $fats = round($calories * 0.30 / 9);
    $carbs = round(($calories - ($protein * 4) - ($fats * 9)) / 4);
} elseif ($goal === 'endurance') {
    $protein = round($weight_kg * 1.6);
    $fats = round($calories * 0.20 / 9);
    $carbs = round(($calories - ($protein * 4) - ($fats * 9)) / 4);
} else {
    $protein = round($weight_kg * 1.8);
    $fats = round($calories * 0.28 / 9);
    $carbs = round(($calories - ($protein * 4) - ($fats * 9)) / 4);
}

// Workout plans
$gym_beginner = [
    'Monday' => ['name' => 'Upper Body (Push)', 'focus' => 'Chest, Shoulders, Triceps', 'exercises' => [
        ['name' => 'Bench Press', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Dumbbell Shoulder Press', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Incline Dumbbell Press', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Lateral Raises', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
        ['name' => 'Tricep Pushdown', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
    ]],
    'Tuesday' => ['name' => 'Lower Body', 'focus' => 'Quads, Hamstrings, Glutes', 'exercises' => [
        ['name' => 'Squat', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Leg Press', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s'],
        ['name' => 'Romanian Deadlift', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s'],
        ['name' => 'Leg Curl', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
        ['name' => 'Calf Raises', 'sets' => '4', 'reps' => '15-20', 'rest' => '30s'],
    ]],
    'Wednesday' => ['name' => 'Rest / Active Recovery', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Light Walk', 'sets' => '1', 'reps' => '30 mins', 'rest' => '-'],
        ['name' => 'Full Body Stretching', 'sets' => '1', 'reps' => '20 mins', 'rest' => '-'],
    ]],
    'Thursday' => ['name' => 'Upper Body (Pull)', 'focus' => 'Back, Biceps', 'exercises' => [
        ['name' => 'Lat Pulldown', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Seated Cable Row', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s'],
        ['name' => 'Dumbbell Row', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Bicep Curl', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
        ['name' => 'Hammer Curl', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
    ]],
    'Friday' => ['name' => 'Full Body + Core', 'focus' => 'Full Body, Abs', 'exercises' => [
        ['name' => 'Deadlift', 'sets' => '3', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'Goblet Squat', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Plank', 'sets' => '3', 'reps' => '30-45s', 'rest' => '45s'],
        ['name' => 'Crunches', 'sets' => '3', 'reps' => '15-20', 'rest' => '30s'],
        ['name' => 'Russian Twists', 'sets' => '3', 'reps' => '20', 'rest' => '30s'],
    ]],
    'Saturday' => ['name' => 'Cardio / Conditioning', 'focus' => 'Cardio', 'exercises' => [
        ['name' => 'Treadmill Run', 'sets' => '1', 'reps' => '20-30 mins', 'rest' => '-'],
        ['name' => 'Jump Rope', 'sets' => '5', 'reps' => '2 mins', 'rest' => '60s'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Rest & Recharge', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
        ['name' => 'Optional: Yoga or Foam Rolling', 'sets' => '1', 'reps' => '20 mins', 'rest' => '-'],
    ]],
];

$gym_intermediate = [
    'Monday' => ['name' => 'Chest + Triceps', 'focus' => 'Chest, Triceps', 'exercises' => [
        ['name' => 'Barbell Bench Press', 'sets' => '4', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'Incline DB Press', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Cable Flyes', 'sets' => '3', 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Close Grip Bench Press', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Skull Crushers', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Tricep Dips', 'sets' => '3', 'reps' => 'Failure', 'rest' => '60s'],
    ]],
    'Tuesday' => ['name' => 'Back + Biceps', 'focus' => 'Back, Biceps', 'exercises' => [
        ['name' => 'Deadlift', 'sets' => '4', 'reps' => '5-6', 'rest' => '180s'],
        ['name' => 'Pull-Ups', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Barbell Row', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Face Pulls', 'sets' => '3', 'reps' => '15-20', 'rest' => '45s'],
        ['name' => 'Barbell Curl', 'sets' => '4', 'reps' => '8-10', 'rest' => '60s'],
        ['name' => 'Incline DB Curl', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
    ]],
    'Wednesday' => ['name' => 'Legs + Core', 'focus' => 'Quads, Hamstrings, Glutes, Core', 'exercises' => [
        ['name' => 'Barbell Squat', 'sets' => '5', 'reps' => '5', 'rest' => '180s'],
        ['name' => 'Leg Press', 'sets' => '4', 'reps' => '10-12', 'rest' => '90s'],
        ['name' => 'Bulgarian Split Squat', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s'],
        ['name' => 'Lying Leg Curl', 'sets' => '4', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Hanging Leg Raises', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
        ['name' => 'Cable Crunch', 'sets' => '3', 'reps' => '15-20', 'rest' => '30s'],
    ]],
    'Thursday' => ['name' => 'Rest / Active Recovery', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Foam Rolling', 'sets' => '1', 'reps' => '15 mins', 'rest' => '-'],
        ['name' => 'Mobility Work', 'sets' => '1', 'reps' => '20 mins', 'rest' => '-'],
    ]],
    'Friday' => ['name' => 'Shoulders + Arms', 'focus' => 'Shoulders, Arms', 'exercises' => [
        ['name' => 'OHP Barbell Press', 'sets' => '4', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'Arnold Press', 'sets' => '3', 'reps' => '10-12', 'rest' => '75s'],
        ['name' => 'Lateral Raises', 'sets' => '4', 'reps' => '12-15', 'rest' => '45s'],
        ['name' => 'Rear Delt Flyes', 'sets' => '3', 'reps' => '15-20', 'rest' => '45s'],
        ['name' => 'Superset: Curl + Pushdown', 'sets' => '4', 'reps' => '10-12', 'rest' => '60s'],
    ]],
    'Saturday' => ['name' => 'Full Body Power', 'focus' => 'Compound Movements', 'exercises' => [
        ['name' => 'Power Cleans', 'sets' => '4', 'reps' => '4-6', 'rest' => '120s'],
        ['name' => 'Front Squat', 'sets' => '3', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'Weighted Pull-Ups', 'sets' => '3', 'reps' => '6-8', 'rest' => '90s'],
        ['name' => 'Farmer Carries', 'sets' => '4', 'reps' => '30m', 'rest' => '60s'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Complete Rest', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
    ]],
];

$gym_expert = [
    'Monday' => ['name' => 'Heavy Chest + Power', 'focus' => 'Chest, Power', 'exercises' => [
        ['name' => 'Paused Bench Press', 'sets' => '5', 'reps' => '3-5', 'rest' => '180s'],
        ['name' => 'Weighted Dips', 'sets' => '4', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'DB Incline Press', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Cable Crossover', 'sets' => '3', 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Overhead Tricep Extension', 'sets' => '4', 'reps' => '8-12', 'rest' => '60s'],
    ]],
    'Tuesday' => ['name' => 'Squat Focus', 'focus' => 'Legs, Posterior Chain', 'exercises' => [
        ['name' => 'Competition Squat', 'sets' => '6', 'reps' => '2-4', 'rest' => '240s'],
        ['name' => 'Front Squat', 'sets' => '4', 'reps' => '4-6', 'rest' => '180s'],
        ['name' => 'Hack Squat', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Leg Curl + Extension', 'sets' => '4', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Standing Calf Raises', 'sets' => '5', 'reps' => '15-20', 'rest' => '30s'],
    ]],
    'Wednesday' => ['name' => 'Pull Day (Heavy)', 'focus' => 'Back, Rear Delts, Biceps', 'exercises' => [
        ['name' => 'Sumo Deadlift', 'sets' => '5', 'reps' => '3-5', 'rest' => '240s'],
        ['name' => 'Weighted Pull-Ups', 'sets' => '4', 'reps' => '6-8', 'rest' => '120s'],
        ['name' => 'Pendlay Row', 'sets' => '4', 'reps' => '5-6', 'rest' => '150s'],
        ['name' => 'Meadows Row', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'EZ Bar Curl 21s', 'sets' => '4', 'reps' => '21', 'rest' => '60s'],
    ]],
    'Thursday' => ['name' => 'Active Recovery + Mobility', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Yoga / Stretching', 'sets' => '1', 'reps' => '30 mins', 'rest' => '-'],
        ['name' => 'Light Cardio', 'sets' => '1', 'reps' => '20 mins', 'rest' => '-'],
    ]],
    'Friday' => ['name' => 'Shoulders + Arms (Volume)', 'focus' => 'Shoulders, Arms', 'exercises' => [
        ['name' => 'Push Press', 'sets' => '5', 'reps' => '4-5', 'rest' => '180s'],
        ['name' => 'DB Arnold Press', 'sets' => '4', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Cable Lateral Raise', 'sets' => '4', 'reps' => '15-20', 'rest' => '30s'],
        ['name' => 'Drag Curl', 'sets' => '4', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'JM Press', 'sets' => '4', 'reps' => '8-10', 'rest' => '60s'],
    ]],
    'Saturday' => ['name' => 'Olympic / Conditioning', 'focus' => 'Power, Cardio', 'exercises' => [
        ['name' => 'Power Clean & Jerk', 'sets' => '5', 'reps' => '3', 'rest' => '180s'],
        ['name' => 'Box Jumps', 'sets' => '4', 'reps' => '6', 'rest' => '90s'],
        ['name' => 'Battle Ropes', 'sets' => '5', 'reps' => '30s', 'rest' => '30s'],
        ['name' => 'Sled Push', 'sets' => '4', 'reps' => '20m', 'rest' => '60s'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Complete Rest', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
    ]],
];

$home_beginner = [
    'Monday' => ['name' => 'Upper Body Push', 'focus' => 'Chest, Shoulders, Triceps', 'exercises' => [
        ['name' => 'Push-Ups', 'sets' => '3', 'reps' => '8-12', 'rest' => '60s'],
        ['name' => 'Pike Push-Ups', 'sets' => '3', 'reps' => '8-10', 'rest' => '60s'],
        ['name' => 'Tricep Dips (Chair)', 'sets' => '3', 'reps' => '10-12', 'rest' => '45s'],
        ['name' => 'Diamond Push-Ups', 'sets' => '2', 'reps' => '6-10', 'rest' => '60s'],
    ]],
    'Tuesday' => ['name' => 'Lower Body', 'focus' => 'Legs, Glutes', 'exercises' => [
        ['name' => 'Bodyweight Squats', 'sets' => '3', 'reps' => '15-20', 'rest' => '45s'],
        ['name' => 'Glute Bridges', 'sets' => '3', 'reps' => '15-20', 'rest' => '30s'],
        ['name' => 'Reverse Lunges', 'sets' => '3', 'reps' => '10 each', 'rest' => '45s'],
        ['name' => 'Wall Sit', 'sets' => '3', 'reps' => '30-45s', 'rest' => '45s'],
        ['name' => 'Calf Raises', 'sets' => '3', 'reps' => '20-25', 'rest' => '30s'],
    ]],
    'Wednesday' => ['name' => 'Rest / Light Cardio', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Brisk Walk', 'sets' => '1', 'reps' => '30 mins', 'rest' => '-'],
        ['name' => 'Stretching', 'sets' => '1', 'reps' => '15 mins', 'rest' => '-'],
    ]],
    'Thursday' => ['name' => 'Upper Body Pull + Core', 'focus' => 'Back, Core', 'exercises' => [
        ['name' => 'Superman Hold', 'sets' => '3', 'reps' => '10-12', 'rest' => '45s'],
        ['name' => 'Doorframe Row (or Towel Row)', 'sets' => '3', 'reps' => '10-12', 'rest' => '60s'],
        ['name' => 'Plank', 'sets' => '3', 'reps' => '30-45s', 'rest' => '45s'],
        ['name' => 'Bicycle Crunches', 'sets' => '3', 'reps' => '15-20', 'rest' => '30s'],
        ['name' => 'Dead Bug', 'sets' => '3', 'reps' => '10 each', 'rest' => '30s'],
    ]],
    'Friday' => ['name' => 'Full Body Circuit', 'focus' => 'Full Body', 'exercises' => [
        ['name' => 'Burpees', 'sets' => '3', 'reps' => '8-10', 'rest' => '60s'],
        ['name' => 'Jump Squats', 'sets' => '3', 'reps' => '10-12', 'rest' => '45s'],
        ['name' => 'Push-Ups', 'sets' => '3', 'reps' => '10-15', 'rest' => '45s'],
        ['name' => 'Mountain Climbers', 'sets' => '3', 'reps' => '30s', 'rest' => '30s'],
        ['name' => 'High Knees', 'sets' => '3', 'reps' => '30s', 'rest' => '30s'],
    ]],
    'Saturday' => ['name' => 'Cardio + Flexibility', 'focus' => 'Cardio', 'exercises' => [
        ['name' => 'Jump Rope / Running', 'sets' => '1', 'reps' => '20-30 mins', 'rest' => '-'],
        ['name' => 'Yoga / Stretching', 'sets' => '1', 'reps' => '20 mins', 'rest' => '-'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Rest & Recover', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
    ]],
];

$home_intermediate = [
    'Monday' => ['name' => 'Push Power', 'focus' => 'Chest, Shoulders, Triceps', 'exercises' => [
        ['name' => 'Archer Push-Ups', 'sets' => '4', 'reps' => '8-10 each', 'rest' => '75s'],
        ['name' => 'Handstand Push-Up Progression', 'sets' => '4', 'reps' => '5-8', 'rest' => '90s'],
        ['name' => 'Pseudo Planche Push-Ups', 'sets' => '3', 'reps' => '8-10', 'rest' => '75s'],
        ['name' => 'Tricep Dips (Elevated)', 'sets' => '3', 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Pike Push-Up Holds', 'sets' => '3', 'reps' => '5s hold x 8', 'rest' => '45s'],
    ]],
    'Tuesday' => ['name' => 'Legs + Plyometrics', 'focus' => 'Legs, Power', 'exercises' => [
        ['name' => 'Pistol Squat Progression', 'sets' => '4', 'reps' => '5-8 each', 'rest' => '90s'],
        ['name' => 'Jump Squats', 'sets' => '4', 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Single Leg Hip Thrust', 'sets' => '3', 'reps' => '12-15', 'rest' => '60s'],
        ['name' => 'Nordic Hamstring Curl', 'sets' => '3', 'reps' => '6-10', 'rest' => '75s'],
        ['name' => 'Box Jumps', 'sets' => '4', 'reps' => '8', 'rest' => '60s'],
    ]],
    'Wednesday' => ['name' => 'Active Recovery', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Yoga Flow', 'sets' => '1', 'reps' => '30 mins', 'rest' => '-'],
        ['name' => 'Foam Rolling', 'sets' => '1', 'reps' => '15 mins', 'rest' => '-'],
    ]],
    'Thursday' => ['name' => 'Pull + Core', 'focus' => 'Back, Biceps, Core', 'exercises' => [
        ['name' => 'Pull-Ups', 'sets' => '4', 'reps' => '8-12', 'rest' => '90s'],
        ['name' => 'Chin-Ups', 'sets' => '3', 'reps' => '8-10', 'rest' => '90s'],
        ['name' => 'Inverted Rows (Table)', 'sets' => '3', 'reps' => '10-15', 'rest' => '60s'],
        ['name' => 'L-Sit Hold', 'sets' => '3', 'reps' => '10-20s', 'rest' => '45s'],
        ['name' => 'Hanging Knee Raises', 'sets' => '3', 'reps' => '12-15', 'rest' => '45s'],
    ]],
    'Friday' => ['name' => 'Full Body HIIT', 'focus' => 'Conditioning, Power', 'exercises' => [
        ['name' => 'Burpee Pull-Ups', 'sets' => '4', 'reps' => '6-8', 'rest' => '75s'],
        ['name' => 'Plyometric Push-Ups', 'sets' => '4', 'reps' => '8-10', 'rest' => '75s'],
        ['name' => 'Squat Jumps', 'sets' => '4', 'reps' => '12', 'rest' => '45s'],
        ['name' => 'Mountain Climbers', 'sets' => '4', 'reps' => '30s', 'rest' => '30s'],
        ['name' => 'Tuck Jumps', 'sets' => '3', 'reps' => '10', 'rest' => '45s'],
    ]],
    'Saturday' => ['name' => 'Endurance + Skills', 'focus' => 'Cardio, Skills', 'exercises' => [
        ['name' => 'Handstand Practice', 'sets' => '5', 'reps' => '20-30s hold', 'rest' => '60s'],
        ['name' => 'Running / Cycling', 'sets' => '1', 'reps' => '30-45 mins', 'rest' => '-'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Complete Rest', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
    ]],
];

$home_expert = [
    'Monday' => ['name' => 'Strength Calisthenics', 'focus' => 'Planche, Ring Work', 'exercises' => [
        ['name' => 'Planche Push-Up Progression', 'sets' => '5', 'reps' => '3-6', 'rest' => '180s'],
        ['name' => 'Ring Dips', 'sets' => '4', 'reps' => '8-10', 'rest' => '120s'],
        ['name' => 'One-Arm Push-Up Neg.', 'sets' => '4', 'reps' => '3-5 each', 'rest' => '120s'],
        ['name' => 'Ring Push-Ups', 'sets' => '3', 'reps' => '10-15', 'rest' => '90s'],
        ['name' => 'Weighted Dips', 'sets' => '3', 'reps' => '6-8', 'rest' => '120s'],
    ]],
    'Tuesday' => ['name' => 'Leg Power + Plyos', 'focus' => 'Legs, Explosiveness', 'exercises' => [
        ['name' => 'Pistol Squats', 'sets' => '5', 'reps' => '8-10 each', 'rest' => '90s'],
        ['name' => 'Shrimp Squats', 'sets' => '4', 'reps' => '6-8 each', 'rest' => '75s'],
        ['name' => 'Depth Jumps', 'sets' => '4', 'reps' => '6', 'rest' => '90s'],
        ['name' => 'Nordic Curls', 'sets' => '4', 'reps' => '5-8', 'rest' => '90s'],
        ['name' => 'Single Leg Calf Raises (Weighted)', 'sets' => '4', 'reps' => '15-20', 'rest' => '30s'],
    ]],
    'Wednesday' => ['name' => 'Front Lever + Pull Skills', 'focus' => 'Back, Core Skills', 'exercises' => [
        ['name' => 'Front Lever Progression', 'sets' => '5', 'reps' => '5-10s hold', 'rest' => '180s'],
        ['name' => 'One-Arm Pull-Up Neg.', 'sets' => '4', 'reps' => '3-5 each', 'rest' => '150s'],
        ['name' => 'Weighted Pull-Ups', 'sets' => '4', 'reps' => '5-7', 'rest' => '120s'],
        ['name' => 'Dragon Flag', 'sets' => '3', 'reps' => '5-8', 'rest' => '90s'],
        ['name' => 'Human Flag Practice', 'sets' => '3', 'reps' => '5s hold', 'rest' => '120s'],
    ]],
    'Thursday' => ['name' => 'Recovery + Mobility', 'focus' => 'Recovery', 'exercises' => [
        ['name' => 'Active Recovery Yoga', 'sets' => '1', 'reps' => '45 mins', 'rest' => '-'],
    ]],
    'Friday' => ['name' => 'Muscle-Up + Skills', 'focus' => 'Full Body Skills', 'exercises' => [
        ['name' => 'Muscle-Ups', 'sets' => '5', 'reps' => '5-8', 'rest' => '180s'],
        ['name' => 'Ring Muscle-Ups', 'sets' => '3', 'reps' => '3-5', 'rest' => '180s'],
        ['name' => 'Back Lever', 'sets' => '3', 'reps' => '10s hold', 'rest' => '90s'],
        ['name' => 'Typewriter Pull-Ups', 'sets' => '3', 'reps' => '5-8', 'rest' => '90s'],
    ]],
    'Saturday' => ['name' => 'HIIT + Conditioning', 'focus' => 'Conditioning', 'exercises' => [
        ['name' => 'Sprint Intervals', 'sets' => '8', 'reps' => '30s sprint / 30s rest', 'rest' => '-'],
        ['name' => 'Burpee Pull-Ups', 'sets' => '5', 'reps' => '10', 'rest' => '60s'],
        ['name' => 'Jump Rope (Double Under)', 'sets' => '5', 'reps' => '50 reps', 'rest' => '30s'],
    ]],
    'Sunday' => ['name' => 'Rest Day', 'focus' => 'Full Recovery', 'exercises' => [
        ['name' => 'Complete Rest', 'sets' => '-', 'reps' => '-', 'rest' => '-'],
    ]],
];

// Select plan based on workout type and experience
if ($workout_type === 'gym') {
    if ($experience === 'beginner') $plan = $gym_beginner;
    elseif ($experience === 'intermediate') $plan = $gym_intermediate;
    else $plan = $gym_expert;
} else {
    if ($experience === 'beginner') $plan = $home_beginner;
    elseif ($experience === 'intermediate') $plan = $home_intermediate;
    else $plan = $home_expert;
}

// Save to DB
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Save profile
$stmt = $conn->prepare("INSERT INTO user_profiles (user_id, gender, age, weight, weight_unit, height, height_unit, experience, goal, metabolism, workout_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE gender=VALUES(gender), age=VALUES(age), weight=VALUES(weight), weight_unit=VALUES(weight_unit), height=VALUES(height), height_unit=VALUES(height_unit), experience=VALUES(experience), goal=VALUES(goal), metabolism=VALUES(metabolism), workout_type=VALUES(workout_type)");
$stmt->bind_param("isiisssssss", $user_id, $gender, $age, $weight, $weight_unit, $height, $height_unit, $experience, $goal, $metabolism, $workout_type);
$stmt->execute();

// Save plan
$plan_json = json_encode($plan);
$stmt2 = $conn->prepare("INSERT INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data) VALUES (?, ?, ?, ?, ?, ?)");
$stmt2->bind_param("iiiiis", $user_id, $calories, $protein, $carbs, $fats, $plan_json);
$stmt2->execute();

$conn->close();

echo json_encode([
    'success' => true,
    'nutrition' => [
        'calories' => $calories,
        'protein' => $protein,
        'carbs' => $carbs,
        'fats' => $fats,
    ],
    'plan' => $plan,
    'goal' => $goal,
    'experience' => $experience,
    'workout_type' => $workout_type,
]);
?>
