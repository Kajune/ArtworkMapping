import hashlib

salt = 'thisissalt'

password = input('Enter Password: ')
print(hashlib.sha256((password + salt).encode()).hexdigest())
