function removeCookieByName(name) {
  const cookieName = name;
  const pastDate = 'Thu, 01 Jan 1970 00:00:00 GMT';

  document.cookie = `${cookieName}=; expires=${pastDate}; path=/; SameSite=Lax; Secure`;

  console.log(`Attempted to remove cookie "${cookieName}". Check browser developer tools to confirm.`);
}

const cookieNameToRemove = 'allow-glitchtip';
removeCookieByName(cookieNameToRemove);
