# rebuild web
web_stable:
	cd web && docker build . -t web_stable
	docker stop web_stable || true
	docker rm web_stable || true
	docker run -d -p 5001:3000 --name web_stable --restart=unless-stopped web_stable

# rebuild admin
admin_stable:
	cd frontend && docker build . -t admin_stable
	docker stop admin_stable || true
	docker rm admin_stable || true
	docker run -d -p 3001:80 --name admin_stable --restart=unless-stopped admin_stable

# rebuild web
web_release:
	cd web && docker build . -t web_release
	docker stop web_release || true
	docker rm web_release || true
	docker run -d -p 5000:3000 --name web_release --restart=unless-stopped web_release

# rebuild admin
admin_release:
	cd frontend && docker build . -t admin_release
	docker stop admin_release || true
	docker rm admin_release || true
	docker run -d -p 3000:80 --name admin_release --restart=unless-stopped admin_release

