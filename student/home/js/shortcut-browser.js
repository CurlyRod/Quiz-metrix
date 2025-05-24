function clearShortcutLinks() {
    const list = document.getElementById("side-menu");
    const shortcuts = list.querySelectorAll(".shortcut-url");
    shortcuts.forEach(a => {
      if (a.parentElement) {
        a.parentElement.remove();
      }
    });
  }
  
  function getFaviconUrl(siteUrl) {
    const url = new URL(siteUrl);
    return `https://www.google.com/s2/favicons?sz=50&domain_url=${url.origin}`;
  }


function buildShortcutList(urls) {
    clearShortcutLinks();
  
    const list = document.getElementById("side-menu");
    urls.forEach(site => {
      const li = document.createElement("li");
      const a = document.createElement("a");
      a.href = site.url;
      a.classList.add("nav-link", "shortcut-url");
      a.target = "_blank";
      a.rel = "noopener noreferrer";
  
      const img = document.createElement("img");
      img.src = getFaviconUrl(site.url);
      img.alt = "Favicon";
      img.style.marginRight = "15px";
  
      a.appendChild(img);
      a.appendChild(document.createTextNode(site.sitename));
      li.appendChild(a);
      list.appendChild(li);
    });
  }
  