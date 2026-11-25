(() => {
    "use strict";

    const STORAGE_KEY = "soqs_proj_lang";

    const DICTIONARY = {
        // 1. تسجيل الدخول والعامة

        "Sign in to your examination portal": "سجّل الدخول إلى بوابة الاختبارات",
        "Sign In": "تسجيل الدخول",
        "Student Register": "تسجيل طالب جديد",
        "Create Student Account": "إنشاء حساب طالب",
        "Email Address": "البريد الإلكتروني",
        "Password": "كلمة المرور",
        "Full Name": "الاسم الكامل",
        "Enter your full name": "أدخل اسمك الكامل",
        "Enter your password": "أدخل كلمة المرور",
        "Demo Accounts:": "حسابات تجريبية:",
        "Log Out": "تسجيل الخروج",
        "↪ Log Out": "↪ تسجيل الخروج",
        "Sign Out": "تسجيل الخروج",
        "↪ Sign Out": "↪ تسجيل الخروج",
        "Back to Dashboard": "العودة للوحة التحكم",
        "← Back to Dashboard": "→ العودة للوحة التحكم",
        "← Back": "→ رجوع",

        // 2. القوائم الجانبية والأدوار
        "ADMIN PANEL": "لوحة الإدارة",
        "INSTRUCTOR PORTAL": "بوابة المحاضر",
        "Student Dashboard": "لوحة تحكم الطالب ",
        "Student Portal": "بوابة الطالب",
        "STUDENT PORTAL": "بوابة الطالب",
        "Recent Results": "النتائج الحديثة",
        "WORKSPACE": "مساحة العمل",
        "MAIN MENU": "القائمة الرئيسية",
        "Dashboard": "لوحة التحكم",
        "◫ Dashboard": "◫ لوحة التحكم",
        "Manage Users": "إدارة المستخدمين",
        "♙ Manage Users": "♙ إدارة المستخدمين",
        "Instructors": "المحاضرون",
        "▣ Instructors": "▣ المحاضرون",
        "Manage Quizzes": "إدارة الاختبارات",
        "▤ Manage Quizzes": "▤ إدارة الاختبارات",
        "Student Results": "نتائج الطلاب",
        "▥ Student Results": "▥ نتائج الطلاب",
        "System Settings": "إعدادات النظام",
        "⚙ System Settings": "⚙ إعدادات النظام",
        "My Quizzes": "اختباراتي",
        "Quizzes": "الاختبارات",
        "Question Bank": "بنك الأسئلة",
        "Questions": "الأسئلة",
        "Results": "النتائج",
        "My Profile": "ملفي الشخصي",
        "Profile": "الملف الشخصي",
        "Available Quizzes": "الاختبارات المتاحة",
        "My Results": "نتائجي",
        "Users": "المستخدمون",
        "Settings": "الإعدادات",
        "Welcome back,": "مرحباً بعودتك،",

        // 3. الإحصائيات والبطاقات
        "Total Users": "إجمالي المستخدمين",
        "Students": "الطلاب",
        "Total Quizzes": "إجمالي الاختبارات",
        "Submissions": "التسليمات",
        "Average Score": "متوسط الدرجات",
        "Total Submissions": "إجمالي التسليمات",
        "Unique Students": "الطلاب الفريدون",
        "Completed": "المكتملة",
        "Pending": "المعلقة",
        "Registered accounts": "الحسابات المسجلة",
        "Student accounts": "حسابات الطلاب",
        "Instructor accounts": "حسابات المحاضرين",
        "Instructor-created quizzes": "الاختبارات المنشأة",
        "Recorded attempts": "المحاولات المسجلة",
        "Across all attempts": "عبر جميع المحاولات",
        "Across recorded attempts": "عبر المحاولات المسجلة",
        "Quizzes you've created": "الاختبارات التي أنشأتها",
        "Saved questions": "الأسئلة المحفوظة",
        "Recorded quiz attempts": "محاولات الاختبار المسجلة",
        "Available in the system": "متاح في النظام",
        "Quizzes submitted": "اختبارات تم تسليمها",
        "Quizzes not yet completed": "اختبارات لم تُنجز بعد",
        "Your average quiz score": "متوسط درجاتك في الاختبارات",
        "Overall Performance": "الأداء العام",

        // 4. عناوين الأقسام والأوصاف
        "YOUR TEACHING SPACE": "مساحتك التعليمية",
        "Recent Quizzes": "أحدث الاختبارات",
        "Latest quizzes created by instructors": "أحدث الاختبارات التي أنشأها المحاضرون",
        "Quick access to your assessments": "وصول سريع إلى تقييماتك",
        "Add and manage student and instructor accounts": "إضافة وإدارة حسابات الطلاب والمحاضرين",
        "Manage Instructors": "إدارة المحاضرين",
        "Instructor accounts registered in the system": "حسابات المحاضرين المسجلة في النظام",
        "Review and manage instructor-created quizzes": "مراجعة وإدارة الاختبارات المنشأة",
        "All recorded attempts and AI proctoring alerts": "جميع المحاولات المسجلة وتنبيهات المراقبة الذكية",
        "System Settings & Database Backup": "إعدادات النظام والنسخ الاحتياطي",
        "Record a manual backup of the MySQL database in the system logs.": "تسجيل نسخة احتياطية يدوية لقاعدة البيانات في سجلات النظام.",
        "System Name": "اسم النظام",
        "Create fixed, random-pool, or lecture-sectioned quizzes and follow student performance.": "أنشئ اختبارات ثابتة أو عشوائية أو مقسمة حسب المحاضرات وتابع أداء الطلاب.",
        "Create and manage your assessments even after publishing.": "أنشئ وأدر تقييماتك حتى بعد نشرها.",
        "Organize questions by course and lecture.": "نظّم الأسئلة حسب المساق والمحاضرة.",
        "Review recorded quiz attempts, grade essays, and inspect AI alerts.": "راجع محاولات الطلاب وصحّح الأسئلة المقالية وتفقد تنبيهات الذكاء الاصطناعي.",
        "Update your account information.": "حدّث معلومات حسابك الشخصي.",
        "Instructor account": "حساب محاضر",
        "Student Account": "حساب طالب",
        "Active Student Account": "حساب طالب نشط",
        "Need help?": "هل تحتاج مساعدة؟",
        "Manage your quizzes, lectures, and student progress from one place.": "أدر اختباراتك ومحاضراتك وتقدم الطلاب من مكان واحد.",
        "Welcome back! Here's your learning overview.": "مرحباً بعودتك! إليك ملخصك التعليمي.",
        "Stay focused, prepare well, and do your best in every quiz.": "حافظ على تركيزك واستعد جيداً لتحقيق أفضل نتيجة.",
        "Scheduled and open exams": "الاختبارات المجدولة والمتاحة",
        "Your latest quiz performance": "أحدث نتائج اختباراتك",
        "EXAMINATION PORTAL": "بوابة الاختبارات",
        "Select an open exam to begin. Upcoming exams show a live countdown timer.": "اختر اختباراً متاحاً للبدء. الاختبارات القادمة تعرض عداداً تنازلياً.",
        "ACCOUNT SETTINGS": "إعدادات الحساب",
        "Manage your personal information and check your academic progress.": "أدر معلوماتك الشخصية وتابع تقدمك الدراسي.",
        "Personal Information": "المعلومات الشخصية",
        "Performance Summary": "ملخص الأداء",
        "Completed Quizzes": "الاختبارات المكتملة",
        "My Quiz Results": "نتائج اختباراتي",
        "View your submitted attempts and review answers when released.": "اعرض محاولاتك المسلمة وراجع الإجابات عند نشرها.",

        // 5. الأزرار والنماذج ومعالج الاختبار
        "View All": "عرض الكل",
        "View all quizzes →": "عرض كل الاختبارات ←",
        "+ Add User": "+ إضافة مستخدم",
        "+ Add Instructor": "+ إضافة محاضر",
        "Add User": "إضافة مستخدم",
        "Edit User": "تعديل مستخدم",
        "Role": "الدور",
        "Save User": "حفظ المستخدم",
        "Save Changes": "حفظ التغييرات",
        "Save Settings & Create Backup": "حفظ الإعدادات وإنشاء نسخة احتياطية",
        "＋ Create New Quiz": "＋ إنشاء اختبار جديد",
        "＋ Create Quiz (3-Step Wizard)": "＋ إنشاء اختبار (3 مراحل)",
        "Create Quiz (3-Step Wizard)": "إنشاء اختبار (3 مراحل)",
        "1. Details & Exam Mode": "1. التفاصيل ونمط الاختبار",
        "2. Write Questions": "2. كتابة الأسئلة",
        "3. Configure & Publish": "3. الإعداد والنشر",
        "Quiz Title": "عنوان الاختبار",
        "Course": "المساق",
        "Duration (minutes)": "المدة (بالدقائق)",
        "Passing Score (%)": "درجة النجاح (%)",
        "Available From": "متاح من",
        "Available Until": "متاح حتى",
        "Exam Mode": "نمط الاختبار",
        "Result Release Policy": "سياسة ظهور النتيجة",
        "Questions Per Page": "عدد الأسئلة في الصفحة",
        "Navigation Rule": "قاعدة التنقل",
        "Save & Next (Step 2) →": "حفظ والتالي (المرحلة 2) ←",
        "Skip / Next (Step 3) →": "تخطي / التالي (المرحلة 3) ←",
        "← Back": "→ السابق",
        "Save as Draft": "حفظ كمسودة",
        "Save & Publish Quiz": "حفظ ونشر الاختبار",
        "＋ Add Question": "＋ إضافة سؤال",
        "＋ Add Course": "＋ إضافة مساق",
        "Add Question": "إضافة سؤال",
        "Lecture Name": "اسم المحاضرة",
        "Question Type": "نوع السؤال",
        "Marks": "الدرجات",
        "Question Text": "نص السؤال",
        "Option A": "الخيار أ (A)",
        "Option B": "الخيار ب (B)",
        "Option C": "الخيار ج (C)",
        "Option D": "الخيار د (D)",
        "Correct Answer": "الإجابة الصحيحة",
        "Save Question": "حفظ السؤال",
        "Cancel": "إلغاء",
        "Edit": "تعديل",
        "Delete": "حذف",
        "Edit / Questions": "تعديل / الأسئلة",
        "Release Results": "نشر النتائج",
        "View Answers": "عرض الإجابات",
        "View / Grade Answers": "عرض / تصحيح الإجابات",
        "Review Answers": "مراجعة الإجابات",
        "Start Quiz": "بدء الاختبار",
        "Not Available Yet": "غير متاح بعد",
        "Exam Closed": "الاختبار مغلق",
        "Submit Examination": "تسليم الاختبار",
        "Next →": "التالي ←",
        "← Previous": "→ السابق",
        "Finish & Submit ✓": "إنهاء وتسليم ✓",
        "Question Navigator": "جدول الأسئلة",
        "AI Proctoring Active": "المراقبة الذكية مفعّلة",
        "Tab switches and focus leaves are monitored automatically.": "تتم مراقبة تبديل التبويبات وخروج المؤشر تلقائياً.",
        "QUIZ SUBMITTED": "تم تسليم الاختبار",
        "Your Result": "نتيجتك",
        "Review My Answers": "مراجعة إجاباتي",
        "Answer Review & Grading": "مراجعة وتصحيح الإجابات",
        "Save Manual Grades": "حفظ الدرجات اليدوية",
        "Hidden by Instructor": "مخفية من قبل المحاضر",
        "Not released yet": "لم تُنشر بعد",

        // 6. رؤوس الجداول وحقول البحث
        "QUIZ TITLE": "عنوان الاختبار",
        "MODE & MARKS": "النمط والدرجات",
        "DURATION": "المدة",
        "STATUS": "الحالة",
        "ACTIONS": "الإجراءات",
        "QUESTION": "السؤال",
        "COURSE / LECTURE": "المساق / المحاضرة",
        "TYPE": "النوع",
        "MARKS": "الدرجات",
        "STUDENT": "الطالب",
        "PROCTORING": "المراقبة",
        "SCORE": "الدرجة",
        "COMPLETED": "تاريخ الإكمال",
        "ANSWERS": "الإجابات",
        "Name": "الاسم",
        "Email": "البريد الإلكتروني",
        "Actions": "الإجراءات",
        "Status": "الحالة",
        "Proctoring Status": "حالة المراقبة",
        "Percentage": "النسبة المئوية",
        "Completed Date": "تاريخ الإكمال",
        "Score": "الدرجة",
        "Answers": "الإجابات",
        "Published": "منشور",
        "Draft": "مسودة",
        "Clean": "سليم",
        "All Courses": "جميع المساقات",
        "All my quizzes": "جميع اختباراتي",
        "No quizzes yet.": "لا توجد اختبارات بعد.",
        "No quizzes completed yet.": "لا توجد اختبارات مكتملة بعد.",
        "No quizzes published yet.": "لا توجد اختبارات منشورة بعد.",
        "No users found.": "لا يوجد مستخدمون.",
        "No quiz results yet.": "لا توجد نتائج اختبارات بعد.",
        "Search quizzes...": "ابحث في الاختبارات...",
        "Search questions...": "ابحث في الأسئلة...",
        "Search by quiz or student...": "ابحث بالاختبار أو الطالب...",
        "No published quizzes available right now.": "لا توجد اختبارات بعد منشورة حتى الان.",
        "Search student, email or quiz": "ابحث بالطالب أو البريد أو الاختبار",
        // خيارات القوائم المنسدلة في معالج الاختبار وبنك الأسئلة
        "1. Fixed Questions (Manual Order)": "1. أسئلة ثابتة (ترتيب يدوي)",
        "2. Random Subset from Pool": "2. سحب عشوائي من مجموعة أسئلة",
        "3. Sectioned by Lecture (Random per Lecture)": "3. مقسم حسب المحاضرات (عشوائي لكل محاضرة)",
        "Immediately after submission": "مباشرة بعد تسليم الاختبار",
        "After exam end date/time": "بعد انتهاء موعد الاختبار",
        "Manually by instructor": "يدوياً من قِبل المحاضر",
        "Allow Backtracking (Previous button enabled)": "السماح بالرجوع (زر السابق مفعّل)",
        "Disable Backtracking (Forward only)": "منع الرجوع (التقدم للأمام فقط)",
        "Multiple Choice (MCQ)": "اختيار من متعدد (MCQ)",
        "Multiple Choice": "اختيار من متعدد",
        "True / False": "صح / خطأ",
        "Essay (Manual Grading)": "مقالي (تصحيح يدوي)",
        "Essay": "سؤال مقالي",
        "True": "صح",
        "False": "خطأ",
        "e.g. Midterm Exam": "مثال: الاختبار النصفي",
        "Hidden": "مخفية",
        "Pending Release": "بانتظار النشر",
        "Completed ✓": "مكتمل ✓",
        "🔒 Forward-only mode (No backtracking)": "🔒 وضع التقدم للأمام فقط (الرجوع معطّل)",
        "Your answers have been recorded. Results will be visible once released by your instructor.": "تم تسجيل إجاباتك بنجاح. ستظهر النتيجة فور نشرها من قِبل المحاضر.",
        // بطاقة عن النظام لكل دور
        "About System": "عن النظام",
        "Smart platform for creating fixed, random, and lecture-sectioned exams with AI proctoring and essay grading.": "منصة ذكية لإنشاء اختبارات ثابتة أو عشوائية أو مقسمة حسب المحاضرات مع مراقبة بالذكاء الاصطناعي وتصحيح الأسئلة المقالية.",
        "Central control panel to manage users, instructors, quizzes, AI cheating alerts, and database backups.": "لوحة تحكم مركزية لإدارة المستخدمين والمحاضرين والاختبارات وتنبيهات الغش بالذكاء الاصطناعي والنسخ الاحتياطي.",
        "Smart examination portal offering scheduled online tests, instant grading, and a fair AI-monitored environment.": "بوابة اختبارات ذكية توفر امتحانات إلكترونية مجدولة وتصحيحاً فورياً وبيئة اختبار عادلة مراقبة بالذكاء الاصطناعي.",
        "Smart Online Quiz System © 2026": "نظام الاختبارات الإلكترونية الذكي © 2026",
        "Configure mode, write questions, and select or configure lecture sections.": "اضبط نمط الاختبار، واكتب الأسئلة، واختر أو خصّص أقسام المحاضرات.",
        "Specify how many random questions and marks per question to draw from each lecture:": "حدّد عدد الأسئلة العشوائية ودرجة كل سؤال للسحب من كل محاضرة:",
        "Draw:": "اسحب:",
        "Marks:": "الدرجة:",
        "Show Results": "إظهار النتيجة",
        "Hide Results": "إخفاء النتيجة",
        "Loading Exam...": "جارٍ تحميل الاختبار...",
        "Show Answers to Students": "عرض الإجابات للطلاب",
        "Allow students to review answers": "السماح للطلاب بمراجعة الإجابات",
        "Hide answers (Show score only)": "إخفاء الإجابات (عرض الدرجة فقط)",
        "Question Types Display Order (Questions inside each type stay randomized):": "ترتيب عرض أنواع الأسئلة (تبقى الأسئلة داخل كل نوع عشوائية):",
        "Default / Mixed Order": "الترتيب الافتراضي / المختلط",
        "1. True/False → 2. Multiple Choice → 3. Essay": "1. صح/خطأ ← 2. اختيار من متعدد ← 3. مقالي",
        "1. Multiple Choice → 2. True/False → 3. Essay": "1. اختيار من متعدد ← 2. صح/خطأ ← 3. مقالي",
        "1. Essay → 2. Multiple Choice → 3. True/False": "1. مقالي ← 2. اختيار من متعدد ← 3. صح/خطأ",
        "Course Bank": "بنك أسئلة المساق",
        "Selected Questions": "الأسئلة المختارة",
        "Questions to draw per student": "عدد الأسئلة المسحوبة لكل طالب",
        "Marks per question": "الدرجة لكل سؤال",
        "Student Answer:": "إجابة الطالب:",
        "(No answer submitted)": "(لم يتم إدخال إجابة)",

        "The deadline for this exam has already passed.": "انتهى الموعد المحدد لتقديم هذا الاختبار."



    };
    // ترجمة النصوص الديناميكية التي تحتوي على أرقام أو أسماء متغيرة
    function translateDynamic(text) {
        const clean = text.replace(/\s+/g, " ").trim();
        if (DICTIONARY[clean]) return DICTIONARY[clean];
        if (text.startsWith("Welcome back, ")) return text.replace("Welcome back, ", "مرحباً بعودتك، ");
        if (text.startsWith("Edit Quiz: ")) return text.replace("Edit Quiz: ", "تعديل الاختبار: ");
        if (text.startsWith("Final Score: ")) return text.replace("Final Score: ", "النتيجة النهائية: ");
        if (text.endsWith(" quizzes")) return text.replace(" quizzes", " اختبارات");
        if (text.endsWith(" questions")) return text.replace(" questions", " أسئلة");
        if (text.endsWith(" results")) return text.replace(" results", " نتائج");
        if (text.endsWith(" result(s)")) return text.replace(" result(s)", " نتيجة");
        if (text.endsWith(" Average Score")) return text.replace(" Average Score", " متوسط الدرجات");
        if (text.startsWith("Page ") && text.includes(" of ")) {
            return text.replace("Page ", "صفحة ").replace(" of ", " من ");
        }
        if (text.startsWith("⚠ Flagged")) {
            return text
                .replace("Unauthorized Copy/Paste Attempt", "محاولة نسخ أو لصق غير مصرح بها")
                .replace("⚠ Flagged", "⚠ سلوك مشبوه")
                .replace("Excessive Tab/Focus Switching", "تبديل متكرر للتبويبات أو النوافذ")
                .replace("Frequent Tab/Window Switching", "تبديل متكرر للتبويبات")
                .replace("Repeated Cursor Exit from Exam Area", "خروج متكرر للمؤشر من منطقة الاختبار")
                .replace("Unusually Fast Exam Completion", "إنهاء الاختبار بسرعة غير طبيعية")
                .replace("Intentional Network Disconnect", "انقطاع متعمد للاتصال بالإنترنت");
        }
        if (text.startsWith("Student: ") && text.includes("Completed: ")) {
            return text
                .replace("Student: ", "الطالب: ")
                .replace(" · Completed: ", " · تاريخ الإكمال: ");
        }

        if (text.includes("(Available:") && text.includes("Qs)")) {
            return text.replace("(Available:", "(المتاح:").replace("Qs)", "أسئلة)");
        }

        if (text.endsWith(" Questions answered")) {
            return text.replace(" Questions answered", " أسئلة مجابة");
        }


        return null;
    }
    function translateTextNodes(root) {
        const SKIP_IDS = new Set(["userName", "welcomeName", "profileDisplayName", "sideName", "profHeaderName"]);
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        let node;
        while ((node = walker.nextNode())) {
            const parent = node.parentElement;
            if (!parent) continue;
            if (parent.tagName === "SCRIPT" || parent.tagName === "STYLE") continue;
            if (SKIP_IDS.has(parent.id)) continue;

            const raw = node.nodeValue;
            const trimmed = raw.trim();
            if (!trimmed) continue;

            const translated = translateDynamic(trimmed);
            if (translated) {
                node.nodeValue = raw.replace(trimmed, translated);
            }
        }

        root.querySelectorAll("input[placeholder], textarea[placeholder]").forEach(el => {
            const ph = el.getAttribute("placeholder");
            if (ph && DICTIONARY[ph]) el.setAttribute("placeholder", DICTIONARY[ph]);
        });
    }

    function injectToggleButton() {
        const sidebarBottom = document.querySelector(".sidebar-bottom");
        // إظهار الزر فقط في الصفحات التي تحتوي على قائمة جانبية (الأدمن، المحاضر، لوحة الطالب)
        if (!sidebarBottom) return;
        if (document.getElementById("soqsLangToggle")) return;

        const lang = localStorage.getItem(STORAGE_KEY) || "en";
        const btn = document.createElement("button");
        btn.id = "soqsLangToggle";
        btn.type = "button";
        btn.textContent = lang === "ar" ? "🌐 English" : "🌐 العربية";
        btn.style.cssText = "width:100%;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;margin:0;";

        btn.addEventListener("click", () => {
            const next = (localStorage.getItem(STORAGE_KEY) || "en") === "ar" ? "en" : "ar";
            localStorage.setItem(STORAGE_KEY, next);
            window.location.reload();
        });

        sidebarBottom.prepend(btn);
    }


    if ((localStorage.getItem(STORAGE_KEY) || "en") === "ar") {
        document.documentElement.dir = "rtl";
        document.documentElement.lang = "ar";
        document.documentElement.style.visibility = "hidden";
    }
    if ((localStorage.getItem(STORAGE_KEY) || "en") === "ar") {
        document.documentElement.dir = "rtl";
        document.documentElement.lang = "ar";
    }

    function initLanguage() {
        const lang = localStorage.getItem(STORAGE_KEY) || "en";
        document.documentElement.style.visibility = "visible";
        injectToggleButton();

        if (lang !== "ar") {
            document.documentElement.dir = "ltr";
            document.documentElement.lang = "en";
            return;
        }

        document.documentElement.dir = "rtl";
        document.documentElement.lang = "ar";
        translateTextNodes(document.body);

        let timer = null;
        const observer = new MutationObserver(() => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                observer.disconnect();
                translateTextNodes(document.body);
                observer.observe(document.body, { childList: true, subtree: true });
            }, 80);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initLanguage);
    } else {
        initLanguage();
    }

})();