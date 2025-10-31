<h2>⚠️ Alerte de stock faible</h2>
<p>Le produit <strong>{{ $product->name }}</strong> est en stock faible.</p>

<ul>
  <li>Quantité actuelle : {{ $product->quantity }}</li>
  <li>Code : {{ $product->sku }}</li>
  <li>Catégorie : {{ $product->category->name ?? 'Non défini' }}</li>


</ul>

<p>Date : {{ now()->format('d/m/Y H:i') }}</p>