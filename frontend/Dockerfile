FROM node:22-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Build for production
RUN npm run build

# Production stage - serve with Nginx
FROM nginx:alpine

# Copy built files to nginx root
COPY --from=builder /app/dist /usr/share/nginx/html

# Use default nginx config
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
