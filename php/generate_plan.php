<?php
/* ============================================
   php/generate_plan.php
   Receives JSON payload from plan.js,
   calculates nutrition + saves plan to DB.
   MEMBER 1: Logs plan_generated activity.
   ============================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'log_activity.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Sanitize + validate inputs
$gender       = trim($data['gender']       ?? '');
$age          = (int)   ($data['age']         ?? 0);
$weight       = (float) ($data['weight']      ?? 0);
$weight_unit  = trim($data['weight_unit']  ?? 'kg');
$height       = (float) ($data['height']      ?? 0);
$height_unit  = trim($data['height_unit']  ?? 'cm');
$experience   = trim($data['experience']   ?? '');
$goal         = trim($data['goal']         ?? '');
$metabolism   = trim($data['metabolism']   ?? '');
$workout_type = trim($data['workout_type'] ?? '');

$allowed_genders    = ['male','female','other'];
$allowed_exp        = ['beginner','intermediate','expert'];
$allowed_goals      = ['bulking','cutting','endurance','general_fitness'];
$allowed_metabolism = ['fast','moderate','slow'];
$allowed_workout    = ['gym','home'];

if (!in_array($gender,       $allowed_genders))    { echo json_encode(['success'=>false,'message'=>'Invalid gender.']);      exit; }
if (!in_array($experience,   $allowed_exp))         { echo json_encode(['success'=>false,'message'=>'Invalid experience.']); exit; }
if (!in_array($goal,         $allowed_goals))       { echo json_encode(['success'=>false,'message'=>'Invalid goal.']);       exit; }
if (!in_array($metabolism,   $allowed_metabolism))  { echo json_encode(['success'=>false,'message'=>'Invalid metabolism.']); exit; }
if (!in_array($workout_type, $allowed_workout))     { echo json_encode(['success'=>false,'message'=>'Invalid workout type.']); exit; }
if ($age < 13 || $age > 80)                        { echo json_encode(['success'=>false,'message'=>'Invalid age.']);         exit; }
if ($weight <= 0)                                   { echo json_encode(['success'=>false,'message'=>'Invalid weight.']);      exit; }
if ($height <= 0)                                   { echo json_encode(['success'=>false,'message'=>'Invalid height.']);      exit; }

// Convert weight to kg
$wKg = $weight_unit === 'lbs' ? $weight * 0.453592 : $weight;

// Convert height to cm
$hCm = $height;
if      ($height_unit === 'inches') $hCm = $height * 2.54;
else if ($height_unit === 'ft')     $hCm = $height * 30.48;

// Mifflin-St Jeor BMR
$bmr = $gender === 'male'
    ? (10 * $wKg) + (6.25 * $hCm) - (5 * $age) + 5
    : (10 * $wKg) + (6.25 * $hCm) - (5 * $age) - 161;

// TDEE
$multipliers = ['beginner' => 1.375, 'intermediate' => 1.55, 'expert' => 1.725];
$tdee = $bmr * ($multipliers[$experience] ?? 1.55);

// Metabolism adjustment
if      ($metabolism === 'fast') $tdee += 250;
else if ($metabolism === 'slow') $tdee -= 150;

// Goal adjustment
if      ($goal === 'bulking')   $tdee += 400;
else if ($goal === 'cutting')   $tdee -= 500;
else if ($goal === 'endurance') $tdee += 100;

$calories = max(1200, round($tdee));
$protein  = round($wKg * ($goal === 'cutting' ? 2.2 : ($goal === 'bulking' ? 2.0 : 1.8)));
$fats     = round($calories * 0.28 / 9);
$carbs    = round(($calories - $protein * 4 - $fats * 9) / 4);

// Save to DB
$user_id = $_SESSION['user_id'];
$conn    = getDBConnection();

// Ensure columns exist (Member 2 soft delete)
$conn->query("ALTER TABLE generated_plans ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) DEFAULT 0");
$conn->query("ALTER TABLE generated_plans ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL");

$plan_data = json_encode([
    'goal'         => $goal,
    'experience'   => $experience,
    'workout_type' => $workout_type,
]);

$stmt = $conn->prepare(
    "INSERT INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("iiiiis", $user_id, $calories, $protein, $carbs, $fats, $plan_data);
$stmt->execute();
$conn->close();

// MEMBER 1 — Log plan generation
$uname = $_SESSION['username'] ?? 'unknown';
logActivity($user_id, $uname, 'plan_generated', "Goal: $goal | Experience: $experience | Type: $workout_type");

echo json_encode([
    'success'      => true,
    'nutrition'    => compact('calories','protein','carbs','fats'),
    'goal'         => $goal,
    'experience'   => $experience,
    'workout_type' => $workout_type,
    'plan'         => null, // plan.js uses its own buildDemoPlan() for the schedule
]);
?>
