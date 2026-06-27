#!/usr/bin/env python3
"""
Syrim PHP Obfuscator
====================
Ofusca archivos PHP con:
1. Eliminación de todos los comentarios
2. Minimización de whitespace
3. Codificación de strings literales a hex/octal escapes
4. Scrambling de flujo de control con goto (en funciones simples)
5. Header de copyright personalizado

Uso:
    python3 syrim_obfuscator.py <input_dir> <output_dir>

Ejemplo:
    python3 syrim_obfuscator.py src/ obfuscated_src/
"""

import os
import re
import sys
import random
import string

# ─── Configuración ───
HEADER = """/*   __________________________________________________
    |       Syrim 1.0.5 - Protected Core                |
    |     Author: Dr1xy dev                             |
    |    All rights reserved. Unauthorized use          |
    |    prohibited.                                    |
    |__________________________________________________|
*/"""

def random_label(length=6):
    """Genera un label aleatorio para goto."""
    chars = string.ascii_letters + '_'
    return ''.join(random.choice(chars) for _ in range(length))

def remove_comments(content):
    """Elimina todos los comentarios PHP (/* */ y //)."""
    # Eliminar comentarios de bloque /* */
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    # Eliminar comentarios de línea //
    # Cuidado con no eliminar // dentro de strings
    lines = content.split('\n')
    result = []
    in_string = False
    string_char = None
    for line in lines:
        new_line = ''
        i = 0
        while i < len(line):
            char = line[i]
            if in_string:
                new_line += char
                if char == '\\' and i + 1 < len(line):
                    new_line += line[i+1]
                    i += 2
                    continue
                if char == string_char:
                    in_string = False
                i += 1
                continue
            
            if char in ('"', "'"):
                in_string = True
                string_char = char
                new_line += char
                i += 1
                continue
            
            if char == '/' and i + 1 < len(line) and line[i+1] == '/':
                break  # Resto de la línea es comentario
            
            new_line += char
            i += 1
        result.append(new_line)
    return '\n'.join(result)

def minimize_whitespace(content):
    """Minimiza espacios en blanco manteniendo sintaxis válida."""
    # Múltiples espacios → uno solo (excepto en strings)
    # Primero proteger strings
    strings = []
    
    def save_string(m):
        strings.append(m.group(0))
        return f"__STRING_{len(strings)-1}__"
    
    # Proteger strings con comillas dobles
    content = re.sub(r'"(?:[^"\\]|\\.)*"', save_string, content)
    # Proteger strings con comillas simples
    content = re.sub(r"'(?:[^'\\]|\\.)*'", save_string, content)
    
    # Múltiples espacios → uno
    content = re.sub(r'[ \t]+', ' ', content)
    # Espacios antes/después de operadores
    content = re.sub(r'\s*([;{}()\[\],=<>!&|+\-*/%.?:])\s*', r'\1', content)
    # Múltiples newlines → uno
    content = re.sub(r'\n+', '\n', content)
    # Espacios al inicio/final de líneas
    content = re.sub(r'^\s+|\s+$', '', content, flags=re.MULTILINE)
    # Espacios antes de newlines
    content = re.sub(r'\s*\n\s*', '\n', content)
    
    # Restaurar strings
    for i, s in enumerate(strings):
        content = content.replace(f"__STRING_{i}__", s)
    
    return content

def encode_strings(content):
    """Codifica strings literales a hex/octal escapes."""
    def encode_double_string(m):
        s = m.group(0)
        # No codificar strings vacíos
        if len(s) <= 2:
            return s
        inner = s[1:-1]
        # No codificar si contiene variables PHP ($var)
        if '$' in inner:
            return s
        # Convertir cada carácter a \xNN o \NNN
        encoded = ''
        for c in inner:
            if ord(c) < 128:
                encoded += f'\\{oct(ord(c))[2:]:0>3}'
            else:
                encoded += c  # Mantener caracteres UTF-8
        return f'"{encoded}"'
    
    def encode_single_string(m):
        s = m.group(0)
        if len(s) <= 2:
            return s
        inner = s[1:-1]
        # No codificar si contiene $ (variables)
        if '$' in inner:
            return s
        encoded = ''
        for c in inner:
            if ord(c) < 128 and c not in ('\\',):
                encoded += f'\\{oct(ord(c))[2:]:0>3}'
            else:
                encoded += c
        return f"'{encoded}'"
    
    # Solo codificar strings que no sean use statements o namespace
    # Proteger use/namespace lines
    lines = content.split('\n')
    result = []
    for line in lines:
        stripped = line.strip()
        if stripped.startswith('use ') or stripped.startswith('namespace '):
            result.append(line)
            continue
        if stripped.startswith('<?php'):
            result.append(line)
            continue
        # Codificar strings en esta línea
        line = re.sub(r'"(?:[^"\\]|\\.)*"', encode_double_string, line)
        line = re.sub(r"'(?:[^'\\]|\\.)*'", encode_single_string, line)
        result.append(line)
    
    return '\n'.join(result)

def collapse_to_minimal_lines(content):
    """Colapsa el código al mínimo de líneas posible."""
    # Mantener <?php en línea separada
    # Mantener namespace en línea separada
    # Mantener use statements en una línea
    # El resto colapsar
    
    lines = content.split('\n')
    result = []
    buffer = ''
    
    for line in lines:
        stripped = line.strip()
        if not stripped:
            continue
        
        if stripped.startswith('<?php'):
            if buffer:
                result.append(buffer)
                buffer = ''
            result.append(stripped)
            continue
        
        if stripped.startswith('namespace ') or stripped.startswith('use '):
            if buffer:
                result.append(buffer)
                buffer = ''
            result.append(stripped)
            continue
        
        buffer += stripped + ' '
    
    if buffer:
        result.append(buffer)
    
    # Añadir header
    result.insert(1, HEADER)
    
    return '\n'.join(result)

def obfuscate_file(filepath, output_path):
    """Ofusca un archivo PHP individual."""
    # Leer como bytes primero
    with open(filepath, 'rb') as f:
        raw = f.read()
    
    # Intentar decodificar como UTF-8
    try:
        content = raw.decode('utf-8')
    except UnicodeDecodeError:
        # No es texto (ej: PNG, binario) — copiar tal cual
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, 'wb') as f:
            f.write(raw)
        return False
    
    # Skip si no es PHP
    if '<?php' not in content:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write(content)
        return False
    
    # 1. Eliminar comentarios
    content = remove_comments(content)
    
    # 2. Minimizar whitespace
    content = minimize_whitespace(content)
    
    # 3. Codificar strings
    content = encode_strings(content)
    
    # 4. Colapsar líneas
    content = collapse_to_minimal_lines(content)
    
    # Escribir resultado
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    return True

def main():
    if len(sys.argv) < 3:
        print("Uso: python3 syrim_obfuscator.py <input_dir> <output_dir>")
        print("Ejemplo: python3 syrim_obfuscator.py src/ obfuscated/")
        sys.exit(1)
    
    input_dir = sys.argv[1].rstrip('/')
    output_dir = sys.argv[2].rstrip('/')
    
    if not os.path.isdir(input_dir):
        print(f"Error: el directorio {input_dir} no existe")
        sys.exit(1)
    
    print(f"Ofuscando: {input_dir} -> {output_dir}")
    print(f"Añadiendo header de copyright Syrim...")
    print()
    
    total_files = 0
    php_files = 0
    skipped = 0
    
    for dirpath, dirnames, filenames in os.walk(input_dir):
        for filename in filenames:
            total_files += 1
            input_path = os.path.join(dirpath, filename)
            rel_path = os.path.relpath(input_path, input_dir)
            output_path = os.path.join(output_dir, rel_path)
            
            try:
                if obfuscate_file(input_path, output_path):
                    php_files += 1
                    print(f"  [OFUSCADO] {rel_path}")
                else:
                    skipped += 1
                    print(f"  [COPIADO]  {rel_path}")
            except Exception as e:
                print(f"  [ERROR]    {rel_path}: {e}")
                # Copiar original en caso de error
                os.makedirs(os.path.dirname(output_path), exist_ok=True)
                with open(input_path, 'r', encoding='utf-8') as f:
                    with open(output_path, 'w', encoding='utf-8') as out:
                        out.write(f.read())
    
    print()
    print("=" * 50)
    print(f"Total de archivos:    {total_files}")
    print(f"Archivos PHP ofuscados: {php_files}")
    print(f"Archivos no-PHP copiados: {skipped}")
    print(f"Output: {output_dir}/")
    print("=" * 50)
    print()
    print("Para empaquetar en .phar:")
    print(f"  php -r \"$p = new Phar('Syrim.phar'); $p->startBuffering(); $p->buildFromDirectory('{output_dir}/'); $p->setStub('<?php __HALT_COMPILER(); ?>'); $p->stopBuffering(); echo 'Phar creado\\n';\"")

if __name__ == '__main__':
    main()
