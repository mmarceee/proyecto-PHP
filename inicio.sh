#!/bin/bash

# Colores para que la terminal se vea profesional
CIAN='\033[0;36m'
VERDE='\033[0;32m'
NC='\033[0m'

echo -e "${CIAN}🚀 Arrancando el motor de Laravel Sail...${NC}"
./vendor/bin/sail up -d

echo -e "${VERDE}🎨 Iniciando servidor de estilos (Vite)...${NC}"
./vendor/bin/sail npm run dev
