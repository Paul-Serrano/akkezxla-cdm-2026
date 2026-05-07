const THEME_STORAGE_KEY = 'theme';

function getTheme() {
	try {
		const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
		if (savedTheme === 'light' || savedTheme === 'dark') {
			return savedTheme;
		}
	} catch (e) {
		// Ignore storage access errors.
	}

	return 'dark';
}

function setTheme(theme) {
	document.documentElement.setAttribute('data-theme', theme);
	updateThemeLabels(theme);

	try {
		localStorage.setItem(THEME_STORAGE_KEY, theme);
	} catch (e) {
		// Ignore storage access errors.
	}
}

function updateThemeLabels(theme) {
	document.querySelectorAll('[data-theme-name]').forEach((label) => {
		label.textContent = theme === 'dark' ? 'Dark' : 'Light';
	});

	const moonSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.599.748-3.752A9.75 9.75 0 1 0 21.752 15.002Z" /></svg>';
	const sunSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5M4.5 12H3m15.364 6.364-1.06-1.06M6.696 6.696l-1.06-1.06m12.728 0-1.06 1.06M6.696 17.304l-1.06 1.06M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>';

	document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
		icon.innerHTML = theme === 'dark' ? moonSvg : sunSvg;
	});
}

function toggleTheme() {
	const currentTheme = document.documentElement.getAttribute('data-theme') || getTheme();
	setTheme(currentTheme === 'dark' ? 'light' : 'dark');
}

function initThemeToggle() {
	setTheme(getTheme());

	if (window.__themeToggleBound) {
		return;
	}

	window.__themeToggleBound = true;

	document.addEventListener('click', (event) => {
		const toggle = event.target.closest('[data-theme-toggle]');
		if (!toggle) {
			return;
		}

		event.preventDefault();
		toggleTheme();
	});
}

document.addEventListener('DOMContentLoaded', initThemeToggle);
document.addEventListener('livewire:navigated', initThemeToggle);
initThemeToggle();
