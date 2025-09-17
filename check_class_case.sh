#!/bin/bash

echo "=== Mulai cek class/trait vs nama file ==="

find app -type f -name "*.php" | while read file; do
    # ambil nama class/trait, tanpa extends/implements
    # menangani abstract class juga
    name=$(grep -m1 -E "^(abstract )?(class|trait) " "$file" | awk '{print $NF}')
    filename=$(basename "$file" .php)
    
    if [ -n "$name" ] && [ "$name" != "$filename" ]; then
        echo "⚠️ Mismatch: $file → '$name', File '$filename.php'"
    fi
done

echo "=== Selesai ==="
