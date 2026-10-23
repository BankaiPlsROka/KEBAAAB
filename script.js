(() => {
  const product = document.querySelector('#produkt_id');
  const quantity = document.querySelector('#ilosc');
  const total = document.querySelector('#total-price');
  const orderForm = document.querySelector('#order-form');
  const errorButton = document.querySelector('#error-button');
  const errorMessage = document.querySelector('#error-message');

  const updateTotal = () => {
    const selected = product?.selectedOptions[0];
    const price = Number(selected?.dataset.price || 0);
    const count = Number(quantity?.value || 0);
    if (!total) return;
    if (!Number.isInteger(count) || count < 1 || count > 5) {
      total.textContent = 'wybierz ilość 1–5';
      return;
    }
    total.textContent = `${(price * count).toFixed(2).replace('.', ',')} zł`;
  };

  product?.addEventListener('change', updateTotal);
  quantity?.addEventListener('input', updateTotal);
  quantity?.addEventListener('change', updateTotal);
  orderForm?.addEventListener('submit', (event) => {
    const count = Number(quantity?.value);
    if (!Number.isInteger(count) || count < 1 || count > 5) {
      event.preventDefault();
      quantity?.setCustomValidity('Dozwolona liczba sztuk: od 1 do 5.');
      quantity?.reportValidity();
      return;
    }
    quantity?.setCustomValidity('');
  });
  quantity?.addEventListener('input', () => quantity.setCustomValidity(''));
  errorButton?.addEventListener('click', () => {
    if (errorMessage) errorMessage.hidden = !errorMessage.hidden;
  });
  updateTotal();
})();
