<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-brand">
        <h1>Data Manager</h1>
      </div>
      <div class="nav-links">
        <router-link to="/custom-fields">Custom Fields</router-link>
        <router-link to="/categories">Categories</router-link>
        <router-link to="/forms">Forms</router-link>
        <router-link to="/submissions">Submissions</router-link>
      </div>
    </nav>
    
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
  background: #f5f7fa;
}

.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.nav-brand h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
}

.nav-links a {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-links a:hover {
  background: rgba(255, 255, 255, 0.2);
}

.nav-links a.router-link-active {
  background: rgba(255, 255, 255, 0.3);
}

.main-content {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 2rem;
}
</style>