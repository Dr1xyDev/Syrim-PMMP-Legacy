<div align="center">

░█▀▀░█░█░█▀▄░▀█▀░█▄█
░▀▀█░░█░░█▀▄░░█░░█░█
░▀▀▀░░▀░░▀░▀░▀▀▀░▀░▀

**Núcleo personalizado para Minecraft: Bedrock Edition v1.1.5 (protocolo v113)**

**Versión:** 1.0.5 | **Autor:** Dr1xy dev | **API:** 3.0.1 (modificada)

</div>

---

## 📋 Índice
- [Acerca de](#acerca-de)
- [Requisitos](#requisitos)
- [Características](#características)
- [Generación de Mundo](#generación-de-mundo)
- [Biomas](#biomas)
- [Instalación](#instalación)
- [Créditos](#créditos)

---

## 📖 Acerca de

Syrim es un núcleo basado en PocketMine-MP, diseñado específicamente para Minecraft: Bedrock Edition v1.1.5 (protocolo v113). Incluye un generador de mundo vanilla porteado desde Nukkit 1.1.5, biomas personalizados, sistema de slots dinámicos, soporte multi-idioma y muchas mejoras de calidad para los administradores de servidores.

> **Nota:** Versiones anteriores existieron pero no fueron publicadas debido a motivos privados.

---

## 🔧 Requisitos

- **PHP:** 7.3 o 7.4 (estables) | 8.0 (experimental por ahora)
- **Minecraft:** Bedrock Edition v1.1.0 - v1.1.7 (protocolo v113)
- **Extensiones:** pthreads, yaml, sockets, curl, zlib
- **Sistema Operativo:** Linux, macOS o Windows

---

## ✨ Características

### Núcleo
- 🔧 **Multi-PHP:** Compatible con PHP 7.3, 7.4 y 8.0 (experimental)
- 🌐 **Multi-Idioma:** Inglés, Ruso, Ucraniano y Español
- 📊 **Slots Dinámicos:** Los slots del servidor muestran `0/1, 1/2, 2/3, 3/4...` (configurable en `syrim.yml`)
- 🎮 **Asistente de Primera Ejecución:** Elige idioma y configura tu servidor al iniciar
- 🔍 **184 TODOs resueltos:** Todos los comentarios TODO/FIXME del código original han sido abordados ( casi resueltos )
- 🪽 **IA de mobs simple:** Se implemento IA simple para casi todos los mobs

### Generación de Mundo
- 🌍 **Generador Vanilla de Nukkit 1.1.5:** Port completo del generador Normal, Flat y Nether
- 🌿 **Ruido Simplex/Perlin:** Algoritmos de ruido porteados para terreno natural
- 🌳 **8 Tipos de Árboles:** Roble, Abedul, Abedul Alto, Pícea, Jungla, Jungla Grande (2x2), Dark Oak (2x2), Sabana (Acacia), Pantano
- 🏔️ **17 Biomas:** Llanuras, Bosque, Desierto, Montañas, Taiga, Pantano, Jungla, Sabana, Hielo, Bosque Oscuro, Isla de Hongos, Meseta, y más
- 🕳️ **Cuevas Naturales:** Túneles serpenteantes con radio variable (no cúbicos)
- 🏞️ **Barrancos Naturales:** Generación orgánica de barrancos con variación de profundidad
- 💎 **Generación de Minerales:** Carbón, Hierro, Redstone, Lapislázuli, Oro, Diamante, Tierra, Grava, Granito, Diorita, Andesita
- 🌾 **Cubierta del Suelo:** Capas superiores específicas por bioma (hierba, arena, arena roja, nieve, micelio)
- 🍄 **Hongos Gigantes:** Generación de hongos rojos y marrones
- 🌵 **Vegetación de Desierto:** Cactus y arbustos secos
- 🌸 **Flores y Hierba:** Populadores para flores, hierba alta, caña de azúcar, lirios

## ⌛ Pendiente

- ⌛ Arreglar bugs de iluminación 
- ⌛ Biomas Totalmente vanilla
- ⌛ Estructuras vanilla
- ⌛ Funciones futuras
- ⌛ IA avanzada de mobs
- ⌛ Añadir funcion de Network Server Query ( conectar slots con varios servers )

---

## 🌍 Generación de Mundo

El generador de Nukkit 1.1.5 ha sido porteado completamente a PHP y es el generador principal de Syrim. Incluye:

| Módulo | Archivos | Descripción |
|--------|----------|-------------|
| **Ruido** | 3 | Simplex, Perlin, base de ruido |
| **Objetos** | 10 | Árboles, vetas de mineral, hongos, hierba |
| **Populadores** | 24 | Cuevas, barrancos, árboles, minerales, flores, cactus, etc. |
| **Biomas** | 27 | 17 biomas activos con configuración completa de populadores |
| **Generadores** | 4 | Normal, Flat, Nether, clase base |

---

## 🌿 Biomas

| Bioma | Características |
|-------|----------------|
| 🌊 Océano | Terreno submarino |
| 🌾 Llanuras | Flores, hierba, caña de azúcar |
| 🏜️ Desierto | Cactus, arbustos secos, arena |
| ⛰️ Montañas | Terreno alto, robles |
| 🍃 Bosque | Árboles de roble densos |
| 🌲 Taiga | Nieve, píceas |
| 🌫️ Pantano | Elevación baja, vines, lirios |
| 🏖️ Playa | Transición de arena |
| 🌳 Bosque de Abedul | Abedules |
| 🌴 Jungla | Árboles de jungla densos, vines, árboles grandes 2x2 |
| 🌳 Sabana | Árboles de acacia (tronco curvo) |
| 🌲 Bosque Oscuro | Árboles dark oak, hongos |
| 🏚️ Bosque Oscuro M | Variante montañosa del bosque oscuro |
| 🍄 Isla de Hongos | Micelio, hongos gigantes |
| 🏔️ Meseta | Arena roja, arbustos secos, mesetas altas |
| ❄️ Llanuras Heladas | Hielo, nieve, píceas |
| 🏞️ Río | Canales de agua |

---

## 📜 Créditos

**Autor:** Dr1xy dev

**Basado en:**
- [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) (LGPL License)
- [Nukkit 1.1.5](https://github.com/CloudburstMC/Nukkit)
- [LiteCore](https://github.com/LiteCoreTeam/LiteCore)
- [GenisysPro](https://github.com/GenisysPro/GenisysPro)

---

<div align="center">

**Syrim se distribuye bajo licencias abiertas que proporcionan acceso libre al núcleo.**

</div>
