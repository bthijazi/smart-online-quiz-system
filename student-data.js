
(function () {
    'use strict';
  
    function read(store, key, fallback) {
      try {
        const value = JSON.parse(store.getItem(key));
        return value == null ? fallback : value;
      } catch {
        return fallback;
      }
    }
  
    const user = read(sessionStorage, 'currentUser', null);
  
    if (!user || user.role !== 'student' || !user.email) {
      window.location.replace('index.html');
      window.StudentData = null;
      return;
    }
  
    const email = String(user.email).trim().toLowerCase();
    const identity = encodeURIComponent(email);
  
    const profileKey = 'soqs:profile:' + identity;
    const reviewKey = 'soqs:reviews:' + identity;
    const isDemo = email === 'student@smartquiz.com';
  
    function array(key) {
      const data = read(localStorage, key, []);
      return Array.isArray(data) ? data : [];
    }
  
    function getProfile() {
      let profile = read(localStorage, profileKey, null);
  
      if (
        !profile ||
        typeof profile !== 'object' ||
        Array.isArray(profile)
      ) {
        const old = isDemo
          ? read(localStorage, 'studentProfile', {})
          : {};
  
        profile = {
          fullName: old.fullName || user.name || 'Student',
          email,
          department: old.department || ''
        };
  
        localStorage.setItem(
          profileKey,
          JSON.stringify(profile)
        );
      }
  
      return { ...profile, email };
    }
  
    function saveProfile(data) {
      const profile = {
        ...getProfile(),
        fullName:
          String(data.fullName || '').trim() ||
          user.name ||
          'Student',
        department: String(data.department || '').trim(),
        email
      };
  
      localStorage.setItem(
        profileKey,
        JSON.stringify(profile)
      );
  
      const session = read(
        sessionStorage,
        'currentUser',
        {}
      );
  
      session.name = profile.fullName;
  
      sessionStorage.setItem(
        'currentUser',
        JSON.stringify(session)
      );
  
      return profile;
    }
  
    function getResults() {
      return array('quizResults').filter(r =>
        r &&
        typeof r === 'object' &&
        (
          String(r.studentEmail || '')
            .trim()
            .toLowerCase() === email ||
          (isDemo && !r.studentEmail)
        )
      );
    }
  
    function saveResult(result, review) {
      const all = array('quizResults');
  
      const attemptId = String(
        result.attemptId || ''
      );
  
      const existing = attemptId
        ? all.find(r =>
            r &&
            r.attemptId === attemptId &&
            String(r.studentEmail || '')
              .toLowerCase() === email
          )
        : null;
  
      if (existing) {
        sessionStorage.setItem(
          'soqs:selectedResultId',
          existing.resultId
        );
  
        return existing;
      }
  
      const resultId = attemptId || (
        'attempt-' +
        Date.now() +
        '-' +
        Math.random().toString(36).slice(2, 9)
      );
  
      const record = {
        ...result,
        resultId,
        studentEmail: email,
        studentName: getProfile().fullName,
        completedAt: new Date().toISOString()
      };
  
      all.push(record);
  
      localStorage.setItem(
        'quizResults',
        JSON.stringify(all)
      );
  
      const reviews = read(
        localStorage,
        reviewKey,
        {}
      );
  
      reviews[resultId] = {
        ...review,
        resultId,
        studentEmail: email
      };
  
      localStorage.setItem(
        reviewKey,
        JSON.stringify(reviews)
      );
  
      sessionStorage.setItem(
        'soqs:selectedResultId',
        resultId
      );
  
      sessionStorage.setItem(
        'quizReviewData',
        JSON.stringify({
          ...review,
          resultId,
          studentEmail: email
        })
      );
  
      return record;
    }
  
    function selectResult(id) {
      sessionStorage.setItem(
        'soqs:selectedResultId',
        String(id)
      );
    }
  
    function getSelectedResult() {
      const id = sessionStorage.getItem(
        'soqs:selectedResultId'
      );
  
      const results = getResults();
  
      return id
        ? results.find(r => r.resultId === id) || null
        : results[results.length - 1] || null;
    }
  
    function getSelectedReview() {
      const result = getSelectedResult();
  
      const reviews = read(
        localStorage,
        reviewKey,
        {}
      );
  
      if (
        result &&
        result.resultId &&
        reviews[result.resultId]
      ) {
        return reviews[result.resultId];
      }
  
      const old = read(
        sessionStorage,
        'quizReviewData',
        null
      );
  
      if (
        old &&
        old.studentEmail === email &&
        (
          !result ||
          !old.resultId ||
          old.resultId === result.resultId
        )
      ) {
        return old;
      }
  
      return null;
    }
  
    function logout(event) {
      if (event) {
        event.preventDefault();
      }
  
      sessionStorage.removeItem('currentUser');
      sessionStorage.removeItem('quizReviewData');
      sessionStorage.removeItem('soqs:selectedResultId');
  
      window.location.href = 'index.html';
    }
  
    window.StudentData = {
      user,
      email,
      getProfile,
      saveProfile,
      getResults,
      saveResult,
      selectResult,
      getSelectedResult,
      getSelectedReview,
      logout
    };
  })();
  