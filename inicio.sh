#!/bin/bash

#!/bin/bash

CIAN='\033[0;36m'
VERDE='\033[0;32m'
AMARILLO='\033[0;33m'
NC='\033[0m'

echo -e "${CIAN}🐳 Comprobando Docker Desktop desde WSL2...${NC}"

# 1. Verificar si el motor responde
if ! docker info > /dev/null 2>&1; then
    echo -e "${AMARILLO}⚠️ Docker Desktop no está corriendo en Windows.${NC}"
    echo -e "${CIAN}🔧 Intentando arrancar Docker Desktop...${NC}"
    
    # 2. Ejecutar el .exe de Windows desde WSL2
    # La ruta suele ser esta por defecto:
    "/mnt/c/Program Files/Docker/Docker/Docker Desktop.exe" & 

    echo -n "⏳ Esperando a que el motor de Docker arranque"
    
    # 3. Bucle de espera (Polling)
    while ! docker info > /dev/null 2>&1; do
        echo -n "."
        sleep 3
    done
    echo -e "\n${VERDE}✅ Docker Desktop está vinculado y listo.${NC}"
else
    echo -e "${VERDE}✅ Docker ya está funcionando.${NC}"
fi

code .

echo -e "${CIAN}🚀 Arrancando Laravel Sail...${NC}"
./vendor/bin/sail up -d

echo -e "⏳ Esperando a que MySQL se inicialice correctamente..."
sleep 10

echo -e "${VERDE}🌟 Iniciando servicios...${NC}"
./vendor/bin/sail npx concurrently -c "cyan,green,magenta,yellow" -n "vite,queue,reverb,sched" \
    "npm run dev" \
    "php artisan queue:work redis --tries=3" \
    "php artisan reverb:start --host=0.0.0.0" \
    "php artisan schedule:work"
