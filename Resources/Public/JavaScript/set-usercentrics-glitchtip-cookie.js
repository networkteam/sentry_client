function setPersistentCookie(name, value, days) {
  let expires = "";
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax; Secure";
}

const cookieName = 'allow-glitchtip';
const cookieValue = 'true';
const expirationInDays = 365;

setPersistentCookie(cookieName, cookieValue, expirationInDays);
