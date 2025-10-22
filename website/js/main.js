const events = [
  {
    id: 1,
    title: "Disney's The Lion King Musical",
    date: 'Fri, Oct 17, 2025 8:00 PM',
    venue: 'Hamburg',
    imageUrl: 'img/lion.png',
    price: '108.99'
  },
  {
    id: 2,
    title: "Macy's Thanksgiving Day Parade",
    date: 'Thu, Nov 27, 2025 9:00 AM',
    venue: 'Manhattan, New York City',
    imageUrl: 'img/macy.png',
    price: '10.00'
  },
  {
    id: 3,
    title: 'Christmas Spectacular Starring the Radio City Rockettes',
    date: 'Sat, Nov 22, 2025 7:00 PM',
    venue: 'Radio City Music Hall, New York',
    imageUrl: 'img/christmas.png',
    price: '49.00'
  },
  {
    id: 4,
    title: "New Year's Eve at Brandenburg Gate",
    date: 'Wed, Dec 31, 2025 7:00 PM',
    venue: 'Brandenburger Tor, Berlin',
    imageUrl: 'img/newyears.png',
    price: '64.00'
  },
  {
    id: 5,
    title: "Times Square New Year's Eve Ball Drop",
    date: 'Wed, Dec 31, 2025 11:59 PM',
    venue: 'Times Square, New York',
    imageUrl: 'img/times.png',
    price: '12.00'
  },
  {
    id: 6,
    title: "Vienna Philharmonic New Year's Concert",
    date: 'Thu, Jan 1, 2026 11:15 AM',
    venue: 'Musikverein, Vienna',
    imageUrl: 'img/vienna.png',
    price: '120.00'
  },
  {
    id: 7,
    title: 'CES 2026 - Consumer Electronics Show',
    date: 'Wed, Jan 7, 2026 9:00 AM',
    venue: 'Las Vegas Convention Center, Las Vegas',
    imageUrl: 'img/ces.png',
    price: '100.00'
  },
  {
    id: 8,
    title: "Edinburgh's Hogmanay Street Party",
    date: 'Wed, Dec 31, 2025 9:00 PM',
    venue: 'City Centre, Edinburgh',
    imageUrl: 'img/edin.png',
    price: '35.00'
  },
  {
    id: 9,
    title: 'Art Basel Miami Beach 2025',
    date: 'Fri, Dec 5, 2025 11:00 AM',
    venue: 'Miami Beach Convention Center, Miami',
    imageUrl: 'img/art.png',
    price: '65.00'
  }
];


function createEventCard(event) {
  return `
    <div class="event-card">
      <div class="event-image-wrapper">
        <img src="${event.imageUrl}" alt="${event.title}" class="event-image">
        <button class="favorite-btn" aria-label="Add to favorites">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
        </button>
        <div class="event-badge">
          <span class="badge-available">Available</span>
        </div>
      </div>

      <div class="event-content">
        <h3 class="event-title">${event.title}</h3>

        <div class="event-details">
          <div class="event-detail">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>${event.date}</span>
          </div>

          <div class="event-detail">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span>${event.venue}</span>
          </div>
        </div>

        <div class="event-footer">
          <span class="event-price-label">From</span>
          <span class="event-price">${event.price}€</span>
        </div>
      </div>
    </div>
  `;
}

function renderEvents() {
  const eventsGrid = document.getElementById('eventsGrid');

  if (!eventsGrid) {
    console.error('Events grid container not found');
    return;
  }

  const eventsHTML = events.map(event => createEventCard(event)).join('');
  eventsGrid.innerHTML = eventsHTML;

  const favoriteButtons = document.querySelectorAll('.favorite-btn');
  favoriteButtons.forEach(btn => {
    btn.addEventListener('click', handleFavoriteClick);
  });
}

function handleFavoriteClick(e) {
  e.stopPropagation();
  const button = e.currentTarget;
  const svg = button.querySelector('svg');

  const isFilled = svg.getAttribute('fill') === 'currentColor';
  svg.setAttribute('fill', isFilled ? 'none' : 'currentColor');

  button.style.transform = 'scale(1.2)';
  setTimeout(() => {
    button.style.transform = 'scale(1)';
  }, 200);
}

document.addEventListener('DOMContentLoaded', () => {
  renderEvents();
});
