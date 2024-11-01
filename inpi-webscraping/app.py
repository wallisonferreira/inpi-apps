import requests
import zipfile
import os
import sqlite3
from io import BytesIO
from bs4 import BeautifulSoup
import pandas as pd
import mysql.connector
import os
import pandas as pd
import xml.etree.ElementTree as ET
import json
from sqlalchemy import create_engine

# Configuração do banco de dados
DATABASE = 'revistas'

def create_database():
    print('Iniciando a criação do banco.')

    cnx = mysql.connector.connect(user='root', password='root',
                                  host='127.0.0.1',
                                  database=DATABASE)
    
    cursor = cnx.cursor()
    cursor.execute("""CREATE TABLE IF NOT EXISTS revistas (
                        numero TEXT, 
                        data TEXT, 
                        url_zip TEXT
                    )""")
    
    # Criação da tabela com todos os campos necessários
    cursor.execute("""CREATE TABLE IF NOT EXISTS dados_xml (
                        numero_revista TEXT,
                        data_revista TEXT,
                        numero_processo TEXT,
                        data_deposito TEXT,
                        data_concessao TEXT,
                        data_vigencia TEXT,
                        despachos TEXT,
                        titulares TEXT,
                        marca_nome TEXT,
                        marca_apresentacao TEXT,
                        marca_natureza TEXT,
                        classes_vienna TEXT,
                        lista_classe_nice LONGTEXT,
                        procurador TEXT
                      )""")
    
    cnx.commit()
    cnx.close()
    #conn.close()
    print('Banco de dados criado!')

def is_new_revista(numero):
    print('Checando se é uma nova revista.')
    cnx = mysql.connector.connect(user='root', password='root',
                                  host='127.0.0.1',
                                  database=DATABASE)
    
    cursor = cnx.cursor()
    cursor.execute("SELECT numero FROM revistas WHERE numero = %s", (numero,))
    result = cursor.fetchone()
    cnx.close()
    print('Revista checada!')
    return result is None

import os
import zipfile
from io import BytesIO
import requests

def fetch_and_download_zip(url_zip, numero_revista):
    print('Iniciando o download do arquivo.')
    response = requests.get(url_zip)
    if response.status_code == 200:
        print('Baixando...')
        zip_file = zipfile.ZipFile(BytesIO(response.content))
        # Define o caminho absoluto ou cria o diretório "revistas" se não existir
        base_path = os.path.join(os.getcwd(), 'revistas')
        os.makedirs(base_path, exist_ok=True)
        
        revista_path = os.path.join(base_path, str(numero_revista))
        os.makedirs(revista_path, exist_ok=True)

        zip_file.extractall(revista_path)
        print('Baixado e inserido em revistas!')
        return revista_path
    else:
        print("Erro ao baixar o arquivo ZIP.")
        return None

def parse_revista(xml_string):
    revista_data = {
        "numero": "",
        "data": "",
        "processos": []
    }

    root = ET.fromstring(xml_string)
    revista_data["numero"] = root.get("numero", "")
    revista_data["data"] = root.get("data", "")

    for processo in root.findall("processo"):
        processo_data = {
            "numero": processo.get("numero", ""),
            "data_deposito": processo.get("data-deposito", ""),
            "data_concessao": processo.get("data-concessao", ""),
            "data_vigencia": processo.get("data-vigencia", ""),
            "despachos": [],
            "titulares": [],
            "marca": {
                "nome": "",
                "apresentacao": "",
                "natureza": ""
            },
            "classes_vienna": [],
            "lista_classe_nice": [],
            "procurador": ""
        }

        # Mapeia os despachos
        for despacho in processo.findall("despachos/despacho"):
            despacho_data = {
                "codigo": despacho.get("codigo", ""),
                "nome": despacho.get("nome", "")
            }
            processo_data["despachos"].append(despacho_data)

        # Mapeia os titulares
        for titular in processo.findall("titulares/titular"):
            titular_data = {
                "nome_razao_social": titular.get("nome-razao-social", ""),
                "pais": titular.get("pais", ""),
                "uf": titular.get("uf", "")
            }
            processo_data["titulares"].append(titular_data)

        # Mapeia a marca, se existir
        marca = processo.find("marca")
        if marca is not None:
            processo_data["marca"]["nome"] = marca.findtext("nome", "").strip()
            processo_data["marca"]["apresentacao"] = marca.get("apresentacao", "")
            processo_data["marca"]["natureza"] = marca.get("natureza", "")

        # Mapeia as classes de Viena, se existirem
        for classe in processo.findall("classes-vienna/classe-vienna"):
            classe_data = {
                "codigo": classe.get("codigo", ""),
                "edicao": classe.get("edicao", "")
            }
            processo_data["classes_vienna"].append(classe_data)

        # Mapeia a lista de classe Nice
        for classe_nice in processo.findall("lista-classe-nice/classe-nice"):
            classe_nice_data = {
                "codigo": classe_nice.get("codigo", ""),
                "especificacao": classe_nice.findtext("especificacao", "").strip(),
                "status": classe_nice.findtext("status", "").strip()
            }
            processo_data["lista_classe_nice"].append(classe_nice_data)

        # Mapeia o procurador, se existir
        procurador = processo.find("procurador")
        processo_data["procurador"] = procurador.text.strip() if procurador is not None else ""

        # Adiciona o processo mapeado à lista de processos
        revista_data["processos"].append(processo_data)

    return revista_data

def process_xml_file(directory):
    all_data = []  # Lista para armazenar dados de todos os arquivos

    for file_name in os.listdir(directory):
        if file_name.endswith('.xml'):
            file_path = os.path.join(directory, file_name)
            with open(file_path, 'r', encoding='utf-8') as file:
                xml_string = file.read()  # Ler o conteúdo do arquivo XML
                revista_data = parse_revista(xml_string)  # Parse do XML

                # Adicionar dados da revista ao resultado
                for processo in revista_data['processos']:
                    all_data.append({
                        'numero_revista': revista_data['numero'],
                        'data_revista': revista_data['data'],
                        'numero_processo': processo['numero'],
                        'data_deposito': processo['data_deposito'],
                        'data_concessao': processo['data_concessao'],
                        'data_vigencia': processo['data_vigencia'],
                        'despachos': json.dumps(processo['despachos']),
                        'titulares': json.dumps(processo['titulares']),
                        'marca_nome': processo['marca']['nome'],
                        'marca_apresentacao': processo['marca']['apresentacao'],
                        'marca_natureza': processo['marca']['natureza'],
                        'classes_vienna': json.dumps(processo['classes_vienna']),
                        'lista_classe_nice': json.dumps(processo['lista_classe_nice']),
                        'procurador': processo['procurador'],
                    })

    # Converte para DataFrame e retorna
    if all_data:  # Verifica se há dados coletados
        return pd.DataFrame(all_data)

    return None  # Retorna None se não houver arquivos XML ou dados

def insert_data_into_database(dataframe, numero_revista, data_revista, url_zip):
    print('Começando a preencher tabela de revistas')
    cnx = mysql.connector.connect(user='root', password='root', host='127.0.0.1', database=DATABASE)
    
    cursor = cnx.cursor()

    # Adicionar o número da revista à tabela de revistas
    cursor.execute("INSERT INTO revistas (numero, data, url_zip) VALUES (%s, %s, %s)", (numero_revista, data_revista, url_zip))
    
    engine = create_engine("mysql+mysqldb://root:root@localhost:3306/revistas")
    # combined.to_sql("cps_raw.cps_basic_tabulation", engine, if_exists='append')
    
    # Inserir os dados do XML processado
    dataframe.to_sql('dados_xml', engine, if_exists='append', index=False)

    cnx.commit()
    cnx.close()
    
def processa_ultimas_revistas(retry=False, numero_revista_continuacao=-1, retry_expired=False):
    
    if retry_expired==True:
        numero_revista_continuacao -=1
    # URL da API (ou faça a requisição diretamente com web scraping)    
    try:
        url_api = 'http://localhost:3000/api/scraping'
        response = requests.get(url_api)

        if response.status_code == 200:
            revistas = response.json()

            for revista in revistas:
                numero_revista = numero_revista_continuacao if retry==True else revista['numeroRevista']
                data_revista = revista['dataRevista']
                url_zip = revista['secaoMarcas'] # Ajuste para a seção correta que contém o link .zip
                
                # print(numero_revista, data_revista, url_zip)
                # print(type(numero_revista), type(data_revista), type(url_zip))
                
                # print('numero extraído: ', numero_revista)

                if is_new_revista(numero_revista):
                    print(numero_revista)
                    print(f'Nova revista encontrada: {numero_revista}')

                    # Baixar o arquivo ZIP
                    download_directory = fetch_and_download_zip(url_zip, numero_revista)
                    if download_directory:
                        # Processar o XML
                        df = process_xml_file(download_directory)
                        if df is not None:
                            # Inserir os dados no banco de dados
                            insert_data_into_database(df, numero_revista, data_revista, url_zip)
                            print(f'Revista {numero_revista} importada com sucesso.')
                        else:
                            print(f'Erro ao processar o XML para a revista {numero_revista}.')
                else:
                    print(f'A revista {numero_revista} já foi processada.')
        else:
            print("Erro ao acessar a API.")

    except Exception as err:
        print(f"Unexpected {err=}, {type(err)=}")
        raise
    
def processa_revistas_antigas(retry=False, numero_revista_continuacao=-1, retry_expired=False):
    if retry_expired==True:
        numero_revista_continuacao -=1

    try:
        # Loop de download das revistas antigas
        total_edicoes = numero_revista_continuacao
        url_base = 'https://revistas.inpi.gov.br/txt/RM{}.zip'
        for index in reversed(range(1, total_edicoes + 1)):
            # Substitui o índice na URL
            url_api = url_base.format(index)
            response = requests.get(url_api)
            
            numero_revista = index
            url_zip = url_api
            
            # Verifica se o texto de erro está no conteúdo da resposta
            if "A URL requisitada" in response.text:
                print(f"Revista {index} não encontrada.")
            else:
                # Se a URL é válida, salva o arquivo
                if is_new_revista(numero_revista):
                    print(numero_revista)
                    print(f'Nova revista encontrada: {numero_revista}')

                    # Baixar o arquivo ZIP
                    download_directory = fetch_and_download_zip(url_zip, numero_revista)
                    if download_directory:
                        # Processar o XML
                        df = process_xml_file(download_directory)
                        if df is not None:
                            # Inserir os dados no banco de dados
                            insert_data_into_database(df, numero_revista, '', url_zip)
                            print(f'Revista {numero_revista} importada com sucesso.')
                        else:
                            print(f'Erro ao processar o XML para a revista {numero_revista}.')
                    else:
                        print(f'A revista {numero_revista} já foi processada.')
    except Exception as err:
        print(f"Erro inesperado {err=}, {type(err)=}. Recompondo")
        print(f"Continuação da extração...")
        if retry==True:
            retry_expired=True
            
            with open('log_error.txt', 'a+') as f:
                f.write('numero_revista: {} | Erro: {}'.format(str(numero_revista_continuacao), err))
                        
        processa_revistas_antigas(retry=True, numero_revista_continuacao=numero_revista_continuacao, retry_expired=retry_expired)
        
    

def main():
    create_database()

    # processa_ultimas_revistas()
                
    processa_revistas_antigas(numero_revista_continuacao=2692)


if __name__ == '__main__':
    main()
