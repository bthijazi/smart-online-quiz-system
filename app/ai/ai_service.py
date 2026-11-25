from flask import Flask, request, jsonify
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import numpy as np

app = Flask(__name__)

# توليد 300 عينة تدريب واقعية لسلوك الطلاب أثناء الاختبار
rng = np.random.default_rng(42)

# 150 حالة طبيعية (Normal = 0)
X_normal = np.column_stack((
    rng.integers(0, 2, size=150),      # tab_switches
    rng.integers(0, 4, size=150),      # mouse_leaves
    rng.integers(0, 3, size=150),      # gaze_deviations
    rng.integers(180, 1800, size=150)  # duration_seconds
))
y_normal = np.zeros(150, dtype=int)

# 150 حالة مشبوهة (Suspicious / Cheating = 1)
X_cheat = np.column_stack((
    rng.integers(3, 15, size=150),
    rng.integers(5, 25, size=150),
    rng.integers(4, 20, size=150),
    rng.integers(15, 1800, size=150)
))
y_cheat = np.ones(150, dtype=int)

X = np.vstack((X_normal, X_cheat))
y = np.concatenate((y_normal, y_cheat))

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
model = DecisionTreeClassifier(max_depth=4, criterion="entropy", random_state=42)
model.fit(X_train, y_train)
acc = round(accuracy_score(y_test, model.predict(X_test)) * 100, 2)


# دالة تحدد السبب الدقيق للاشتباه لعرضه في تقارير المحاضر والأدمن
def determine_reason(tabs, mouse, gaze, duration):
    if tabs >= 3:
        return f"Frequent Tab/Window Switching ({tabs} times)"
    if mouse >= 5:
        return f"Repeated Cursor Exit from Exam Area ({mouse} times)"
    if gaze >= 4:
        return f"Abnormal Gaze Deviations ({gaze} times)"
    if duration < 45:
        return "Unusually Fast Exam Completion"
    return "AI Decision Tree Behavioral Anomaly"


# نقطة الاستقبال التي يتصل بها Laravel لتحليل محاولة الطالب
@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json(silent=True) or {}

    tabs = int(data.get('tab_switches', 0))
    mouse = int(data.get('mouse_leaves', 0))
    gaze = int(data.get('gaze_deviations', 0))
    duration = int(data.get('duration_seconds', 300))

    features = np.array([[tabs, mouse, gaze, duration]])
    pred = int(model.predict(features))
    confidence = round(float(np.max(model.predict_proba(features))), 2)
    is_cheating = (pred == 1)

    return jsonify({
        "is_cheating": is_cheating,
        "confidence": confidence,
        "reason": determine_reason(tabs, mouse, gaze, duration) if is_cheating else "Normal Behavior"
    })


if __name__ == '__main__':
    print(f"AI Decision Tree Service running on http://127.0.0.1:5000 (Accuracy: {acc}%)")
    app.run(host='127.0.0.1', port=5000)
