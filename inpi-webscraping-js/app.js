import express from 'express';
import fetch from 'node-fetch';
import * as cheerio from 'cheerio';

const app = express();
const PORT = 3000;

// Função que faz o web scraping
async function fetchTableData() {
    const url = 'https://revistas.inpi.gov.br/rpi/';

    try {
        // Faz a requisição para o site
        const response = await fetch(url);

        // Verifica se a requisição foi bem-sucedida
        if (!response.ok) {
            throw new Error(`Request failed with status code ${response.status}`);
        }

        // Obtém o corpo da resposta como texto
        const data = await response.text();

        const $ = cheerio.load(data);

        // Seleciona a tabela com base na classe CSS
        const table = $('table.table.table-text-center.table-condensed.table-bordered.table-middle');
        const tableRows = table.find('tbody tr');

        const tableData = [];

        // Itera sobre as linhas da tabela
        tableRows.each((index, element) => {
            const columns = $(element).find('td');
            if (columns.length > 0) {
                // Extrai os dados de cada coluna
                const numeroRevista = $(columns[0]).text().trim();

                // Verifica se numeroRevista é uma string que representa um número
                if (!/^\d+$/.test(numeroRevista)) {
                    // Se não for um número, retorna o resultado imediatamente
                    return false; // Parar a iteração do .each
                }

                const dataRevista = $(columns[1]).text().trim();
                const secaoComunicados = $(columns[2]).find('a').attr('href') || 'N/A';
                const secaoContratos = $(columns[3]).find('a').attr('href') || 'N/A';
                const secaoDesenhosIndustriais = $(columns[4]).find('a').attr('href') || 'N/A';
                const secaoIndicacoesGeograficas = $(columns[5]).find('a').attr('href') || 'N/A';
                const secaoMarcas = $(columns[6]).find('a').filter((i, el) => {
                    const href = $(el).attr('href');
                    return href && href.endsWith('.zip');
                }).attr('href');

                const secaoPatentes = $(columns[7]).find('a').attr('href') || 'N/A';
                const secaoProgramaComputador = $(columns[8]).find('a').attr('href') || 'N/A';
                const secaoTopografias = $(columns[9]).find('a').attr('href') || 'N/A';

                // Armazena os dados em um objeto
                tableData.push({
                    numeroRevista,
                    dataRevista,
                    secaoComunicados,
                    secaoContratos,
                    secaoDesenhosIndustriais,
                    secaoIndicacoesGeograficas,
                    secaoMarcas,
                    secaoPatentes,
                    secaoProgramaComputador,
                    secaoTopografias,
                });
            }
        });

        return tableData;

    } catch (error) {
        console.error('Erro ao buscar os dados:', error.message);
        return { error: 'Erro ao buscar os dados. Verifique se a URL está correta e tente novamente.' };
    }
}

// Rota para acessar a API e obter os dados do web scraping
app.get('/api/scraping', async (req, res) => {
    const data = await fetchTableData();
    res.json(data);
});

// Inicia o servidor
app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT}`);
});
